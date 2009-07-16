<?php // 

require_once("/home/lib/Autoload.php");
require_once(LIB_PATH . 'soap/client/NSS.php');
require_once(LIB_PATH . 'image/NewImage.php');

/**
 * 
 * Backend for netsprint search
 * 
 * Created: 2009-07-14
 * @author Simen Graaten
 * @package
 */

class Search {
    /**
     * Do an article search
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function articleSearch($query, $limit = 6, $page = 0) {
        // Create completing query string
        $query .= "*";

        $nss = new NSS();

        $docType['articles'] = new NSSNameValuePair();
        $docType['articles']->name = 'documentTypeId';
        $docType['articles']->value = 1;

        $autoComplete = new NSSNameValuePair();
        $autoComplete->name = 'autocompleteSearch';
        $autoComplete->value = 'true';

        $sortField = new NSSNameValuePair();
        $sortField->name = 'sortField';
        $sortField->value = 'popularity';

        $sortOrder = new NSSNameValuePair();
        $sortOrder->name = 'sortOrder';
        $sortOrder->value = 0;

        $qp['articles'] = new NSSQueryParameters();
        $qp['articles']->keyword = utf8_encode($query);
        $qp['articles']->pageNr = $page;
        $qp['articles']->pageSize = $limit;
        $qp['articles']->parameters = array($docType['articles'], $autoComplete, $sortField, $sortOrder);

        $qpm = new NSSQueryParametersMap();
        $qpm->keys = array_keys($qp);
        $qpm->values = array_values($qp);

        $s = new NSSSearch();
        $s->queries = $qpm;

        $r = $nss->search($s);

        $return = self::arrayifySoap($r);

        if (!isset($return['data']['articles']))
            $return['data']['articles'] = array();

        return $return;
    }
    
    /**
     * Do a quicksearch query against netsprint for parts of the productname
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function productSearch($query, $limit = 6) {
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

        $return = self::arrayifySoap($r);

        if (!isset($return['data']['products']))
            $return['data']['products'] = array();

        return $return;
    }

    private function arrayifySoap($r) {
        $return = array();
        $return['data'] = array();
        foreach ($r->return->keys as $key => $type) {
            $results = $r->return->values[$key];

            // FIXME: Hack to bypass stupid SOAP default to un-array stuff
            // that only has one hit.
            if (!is_array($results->documents))
                $results->documents = array($results->documents);

            if ($results->documents) {
                foreach ($results->documents as $doc) {
                    if ($doc == null)
                        continue;
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
            $return['hits']['total'] = array_sum($return['hits']);
        }

        return $return;
    }

    
}

?>
