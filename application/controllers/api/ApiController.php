<?php
/**
 * Omeka
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The default controller for API resources.
 * 
 * @package Omeka\Controller
 */
class ApiController extends Omeka_Controller_AbstractActionController
{
    /**
     * Handle GET request without ID.
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        $this->_validateRecordType($request->getParam('api_record_type'));
        
        // Determine the results per page.
        $perPage = (int) get_option('api_per_page');
        $perPageUser = (int) $request->getQuery('per_page');
        if ($perPage > $perPageUser) {
            $perPage = $perPageUser;
        }
        
        $records = $this->_helper->db
            ->getTable($request->getParam('api_record_type'))
            ->findBy($_GET, $perPage, $request->getQuery('page', 1));
        $data = array();
        foreach ($records as $record) {
            $data[] = $this->_getRepresentation($record, $request->getParam('api_resource'));
        }
        $this->_helper->jsonApi($data);
    }
    
    /**
     * Handle GET request with ID.
     */
    public function getAction()
    {
        $request = $this->getRequest();
        $apiParams = $request->getParam('api_params');
        $record = $this->_getRecord($request->getParam('api_record_type'), $apiParams[0]);
        $data = $this->_getRepresentation($record, $request->getParam('api_resource'));
        $this->_helper->jsonApi($data);
    }
    
    /**
     * Return the specified record.
     * 
     * @param string $recordType
     * @param int $id
     * @return Omeka_Record_AbstractRecord
     */
    protected function _getRecord($recordType, $id)
    {
        $this->_validateRecordType($recordType);
        $record = $this->_helper->db->getTable($recordType)->find($id);
        if (!$record) {
            throw new Omeka_Controller_Exception_404('Invalid record. Record not found.');
        }
        return $record;
    }
    
    /**
     * Validate a record type.
     * 
     * @param string $recordType
     */
    protected function _validateRecordType($recordType)
    {
        if (!class_exists($recordType)) {
            throw new Omeka_Controller_Exception_404('Invalid record. Record type not found.');
        }
        if (!in_array('Omeka_Api_RecordInterface', class_implements($recordType))) {
           throw new Omeka_Controller_Exception_404("Invalid record. Record \"$recordType\" must implement Omeka_Api_RecordInterface");
        }
    }
    
    /**
     * Get the representation of a record.
     * 
     * @param Omeka_Record_AbstractRecord $record
     * @param string $resource
     */
    protected function _getRepresentation(Omeka_Record_AbstractRecord $record, $resource)
    {
        $extend = array();
        $extendTemp = apply_filters("api_extend_$resource", array(), array('record' => $record));
        $apiResources = $this->getFrontController()->getParam('api_resources');
        
        // Validate each extended resource. Each must be registered as an API 
        // resource and the content must contain "id" and "url" for one resource 
        // or "count" and "url" for multiple resources.
        foreach ($extendTemp as $extendResource => $extendContent) {
            if (is_array($extendContent) 
                && array_key_exists($extendResource, $apiResources) 
                && (array_key_exists('count', $extendContent) || array_key_exists('id', $extendContent))
                && array_key_exists('url', $extendContent)
            ) {
                $extend[$extendResource] = array('url' => $extendContent['url']);
                if (array_key_exists('id', $extendContent)) {
                    $extend[$extendResource]['id'] = $extendContent['id'];
                } else {
                    $extend[$extendResource]['count'] = $extendContent['count'];
                }
            }
        }
        
        $representation = $record->getRepresentation();
        $representation['extended_resources'] = $extend;
        return $representation;
    }
}
