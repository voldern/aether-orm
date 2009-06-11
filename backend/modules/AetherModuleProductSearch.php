<?php // 

require_once(LIB_PATH . 'Cache.lib.php');
require_once(LIB_PATH . 'soap/client/NSS.php');
require_once(LIB_PATH . 'image/NewImage.php');

/**
 * 
 * Searches for a product title
 * 
 * Created: 2009-05-04
 * @author Simen Graaten
 * @package pg2.backend
 */

class AetherModuleProductSearch extends AetherModule {
    /**
     * Search service:
     *   GET: service=qs
     *        module=PriceguideBackendProductSearch
     *        query=te (part of keyword)
     *        limit=5 (limit)
     */
    public function service($name) {
        $return = array();
        $options = $this->sl->get('aetherConfig')->getOptions();
        if (isset($_GET['query']) AND !empty($_GET['query'])) {
            if (isset($_GET['limit']) AND is_numeric($_GET['limit']))
                $limit = $_GET['limit'];
            else
                $limit = 5;
            // Perform search
            $string = trim($_GET['query']);
            $res = $this->doProductSearch($string);
            if ($res['data']['products']) {
                foreach($res['data']['products'] as $row) {
                    $return[] = $row;
                }
            }
        }

        return new AetherJSONResponse(array('return' => $return));
    }

    /**
     * Do a quicksearch query against netsprint for parts of the productname
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    private function doProductSearch($query, $limit = 6) {
        // Create completing query string
        $query .= "*";

        $nss = new NSS();

        $docType['products'] = new NSSNameValuePair();
        $docType['products']->name = 'documentTypeId';
        $docType['products']->value = 2;

        $autoComplete = new NSSNameValuePair();
        $autoComplete->name = 'autocompleteSearch';
        $autoComplete->value = 'true';

        $sortField = new NSSNameValuePair();
        $sortField->name = 'sortField';
        $sortField->value = 'popularity';

        $sortOrder = new NSSNameValuePair();
        $sortOrder->name = 'sortOrder';
        $sortOrder->value = 0;

        $qp['products'] = new NSSQueryParameters();
        $qp['products']->keyword = utf8_encode($query);
        $qp['products']->pageNr = 0;
        $qp['products']->pageSize = $limit;
        $qp['products']->parameters = array($docType['products'], $autoComplete, $sortField, $sortOrder);

        $qpm = new NSSQueryParametersMap();
        $qpm->keys = array_keys($qp);
        $qpm->values = array_values($qp);

        $s = new NSSSearch();
        $s->queries = $qpm;

        $r = $nss->search($s);

        foreach ($r->return->keys as $key => $type) {
            $results = $r->return->values[$key];

            // FIXME: Hack to bypass stupid SOAP default to un-array stuff
            // that only has one hit.
            if (!is_array($results->documents))
                $results->documents = array($results->documents);

            if ($results->documents) {
                foreach ($results->documents as $doc) {
                    $data = array();

                    if ($doc->attributes) {
                        foreach ($doc->attributes as $att) {
                            $data[$att->name] = $att->value;
                        }
                    }

                    if ($doc->categories) {
                        foreach ($doc->categories as $cat) {
                            $data['categories'][] = array(
                                    'id' => $cat->id,
                                    'name' => $cat->name
                            );
                        }
                    }

                    if (count($data) > 0) 
                        $return['data'][$type][] = $data;
                }
            }

            $return['hits'][$type] = $results->totalCount;
        }

        $return['hits']['total'] = array_sum($return['hits']);

        return $return;
    }

    /**
     * This HAS to be implemented because its abstract
     * A bit stupid yes
     *
     * @access public
     * @return void
     */
    public function run() {
    }
}
?>
