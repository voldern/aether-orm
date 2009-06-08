<?php // 
/**
 * 
 * Show a single product for editing
 * 
 * Created: 2009-06-08
 * @author Espen Volden
 * @package prisguide.backend.modules
 */
require_once(LIB_PATH . 'image/NewImage.php');

class AetherModuleImageImport extends AetherModule {
    /**
     * Render module
     *
     * @access public
     * @return string
     */
    public function run() {
    }

    public function service($name) {
        switch ($name) {
        case 'lookIn':
            $response = $this->serviceLookIn($_GET);
            break;
        case 'connect':
            $response = $this->serviceConnect($_GET);
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
            $qb->addFrom('image_product_link');
            $qb->addSelect('image_id', 'imageId');
            $qb->addSelect('product_id', 'productId');
            $qb->addWhere('product_id', 'IN', $products);
            $productResult = $imgDB->query($qb->build());

            foreach ($productResult as $prod) {
                $result['products'][$prod['productId']][] = $prod['imageId'];
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
