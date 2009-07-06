<?php // 
/**
 * 
 * Show a single product for editing
 * 
 * Created: 2009-06-08
 * @author Espen Volden
 * @package prisguide.backend.modules
 */
require_once(PG_PATH . 'backend/lib/EntityImage.php');
require_once(LIB_PATH . 'image/NewImage.php');
require_once(LIB_PATH . 'upload/UploadManager.lib.php');

class AetherModuleImageImport extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
        $tpl = $this->sl->getTemplate();
        $config = $this->sl->get('aetherConfig');
        $eid = $config->getUrlVar('product_id');
        $tpl->set('eid', $eid);
        return $tpl->fetch("product/image_import.tpl");
    }

    public function service($name) {
        switch ($name) {
        case 'lookIn':
            $response = $this->serviceLookIn($_GET);
            break;
        case 'publish':
            $response = $this->servicePublish($_GET);
            break;
        case 'connect':
            $response = $this->serviceConnect($_GET);
            break;
        case 'unlink':
            $response = $this->serviceUnlink($_GET);
            break;
        case 'upload':
            $files = $_FILES;
            $eid = $_GET['eid'];
            $response = $this->serviceUpload($eid, $files);
            break;
        default:
            $response = array('error' => 'No/Unknown service requested');
        }

        return new AetherJSONResponse($response);
    }

    /**
     * Returns array with image id for articles and products
     *
     * @param array $GET Array that contains articles and products array
     * @return mixed
     */
    private function serviceLookIn($GET) {
        // Check for valid input
        if ((!isset($GET['products']) || empty($GET['products'])) &&
            (!isset($GET['articles']) || empty($GET['articles']))) {
            return array('error' => 'Need list of products and/or articles');
        }

        $imgDB = new Database('images');
        $pgDB = new Database('pg2_backend');
        $result = array('products' => array(), 'articles' => array());

        if (isset($GET['products']) && !empty($GET['products'])) {
            $products = explode(',', $GET['products']);
            foreach ($products as $prod) {
                if (!isset($result['products'][$prod]))
                    $result['products'][$prod] = array();
            }
            
            if (($error = $this->validateIdList($products, 'products')) !== true)
                return array('error' => $error);

            $qb = new QueryBuilder;
            $qb->addFrom('entity_image');
            $qb->addSelect('image_id', 'imageId');
            $qb->addSelect('entity_id', 'entityId');
            $qb->addWhere('entity_id', 'IN', $products);
            $sql = $qb->build();
            $productResult = $pgDB->query($sql);

            foreach ($productResult as $prod) {
                $img = RecordFinder::locate("NewImage", array("id = {$prod['imageId']}"));

                $img = $img->first;
                $res = array(
                    'id' => $img->get('id'),
                    'name' => $img->get('title'),
                    'publishedAt' => $img->get('publishedAt'),
                    'published' => $img->isPublished()
                );
                if (isset($GET['width'])) {
                    if (!isset($GET['height']))
                        $GET['height'] = false;
                    $res['url'] = "http://img.gfx.no" . $img->getSizeUrl($GET['width'], $GET['height']);
                }
                $result['products'][$prod['entityId']][] = $res;
            }
        }

        if (isset($GET['articles']) && !empty($GET['articles'])) {
            $articles = explode(',', $GET['articles']);
            foreach ($articles as $art) {
                if (!isset($result['articles'][$art]))
                    $result['articles'][$art] = array();
            }
                        
            if (($error = $this->validateIdList($articles, 'articles')) !== true)
                return array('error' => $error);

            $qb = new QueryBuilder;
            $qb->addFrom('image_article_link');
            $qb->addSelect('image_id', 'imageId');
            $qb->addSelect('article_id', 'articleId');
            $qb->addWhere('article_id', 'IN', $articles);
            $articleResult = $imgDB->query($qb->build());

            foreach ($articleResult as $art) {
                $result['articles'][$art['articleId']][] = $art['imageId'];
            }
        }

        return $result;
    }

    private function servicePublish($GET) {
        if (!isset($GET['imageId']) || empty($GET['imageId']) ||
            !is_numeric($GET['imageId']))
            return array('error' => 'No/Unknown imageIdd');
        
        // Check if the imageId exists
		try {
			$image = RecordFinder::locate('NewImage', array(
											   "id = {$GET['imageId']}"));
			$image = $image->getByPosition(0);
		} catch (NoRecordsFoundException $e) {
			return array('error' => 'Image not found');
		}
        
        if (isset($_GET['unpublish'])) {
            $image->set('publishedAt', null);
            $image->save();
			return array('return' => array('status' => 'Unpublished'));
        }
        else {
            if (isset($_GET['date'])) {
                $date = $_GET['date'];
                $image->set('publishedAt', $date);
            }
            else {
                $date = date("Y-m-d H:i:s");
                $image->set('publishedAt', $date);
            }
            $image->save();
			return array('return' => array('status' => 'Published', 'date' => $date));
        }
    }

    private function serviceConnect($GET) {
        $acceptedTypes = array('product' => 'Manifestation');

        // Validate imageId, targetId and type
        if (!isset($GET['imageId']) || empty($GET['imageId']) ||
            !is_numeric($GET['imageId']))
            return array('error' => 'No/Unknown imageIdd');
        if (!isset($GET['targetId']) || empty($GET['targetId']) ||
            !is_numeric($GET['targetId']))
            return array('error' => 'No/Unknown targetId');
        if (!isset($GET['type']) || empty($GET['type']) ||
            !isset($acceptedTypes[$GET['type']]))
            return array('error' => 'No/Unknown type');

        // Check if the imageId exists
		try {
			$image = RecordFinder::locate('NewImage', array(
											   "id = {$GET['imageId']}"));
			$image = $image->getByPosition(0);
		} catch (NoRecordsFoundException $e) {
			return array('error' => 'Image not found');
		}

        // Check if the target exists
		try {
			$target = RecordFinder::locate($acceptedTypes[$GET['type']], array(
											   "id = {$GET['targetId']}"));
			$target = $target->getByPosition(0);
		} catch (NoRecordsFoundException $e) {
			return array('error' => 'Target not found');
		}

		// Insert
		$link = new EntityImage;
		$link->set('imageId', $image->get('id'));
		$link->set('entityId', $target->get('id'));
		if ($link->save() !== false)
			return array('status' => 'success');
		else
			return array('error' => 'Unknown error, could not import image');
    }

    /**
     * Unlinks image -> product link
     *
     * @access private
     * @return array
     * @param array $GET
     */
    private function serviceUnlink($GET) {
        if ((!isset($GET['product']) || empty($GET['product'])) ||
            (!isset($GET['image']) || empty($GET['image'])))
            return array('error' => 'Need image and product id');

        try {
            $result = RecordFinder::find('EntityImage', array(
                                             'imageId' => $GET['image'],
                                             'entityId' => $GET['product']));
        } catch (NoRecordsFoundException $e) {
            return array('error' => 'Image -> product link not found');
        }

        foreach ($result->getAll() as $row)
            $row->delete();

        return array('status' => 'success');
    }


    /**
     * Unlinks image -> product link
     *
     * @access private
     * @return array
     * @param array $GET
     */
    private function serviceUpload($eid, $files) {
        if (!isset($eid) || empty($eid))
            return array('error' => 'Need eid');

        $saveDir = "/tmp";

        $um = new UploadManager();
        $um->setSavePath($saveDir);
        $um->addFile($files['Filedata']);
        if ($um->upload()) {
            $uploadFiles = $um->getFiles(); 

            foreach ($uploadFiles as $file) {
                $i = new NewImage(); //FIXME: REMOVE
                $i->set('title', basename($file));
                $i->set('published', 1);
                $i->save();
                $i->uploadFromFile($file);
                
                $ei = new EntityImage();
                $ei->set('entityId', $eid);
                $ei->set('imageId', $i->get('id'));
                $ei->save();
            }
        }

        return array('status' => 'success');
    }



    /**
     * Validates array of id's
     *
     * @param array $items Array of ids to validate
     * @param string $name Name of the array for the error output
     * @return mixed String with error message or true on success
     */
    private function validateIdList($items, $name) {
        if (empty($items) || count($items) == 0)
            return "Format of $name unknown, need CSV";

        // All items should be numeric
        foreach ($items as $item) {
            if (!is_numeric($item))
                return ucfirst($name) . '_id can only be numeric';
        }

        return true;
    }
}
