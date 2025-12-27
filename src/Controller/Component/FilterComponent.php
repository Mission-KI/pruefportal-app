<?php
declare(strict_types=1);

/**
 * Filter component
 * Benefits:
 * 1. Keep the filter criteria in the Session
 * 2. Give ability to customize the search wrapper of the field types
 * *
 *
 * @author  Nik Chankov
 * @website http://nik.chankov.net
 * @version 1.0.0
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;

class FilterComponent extends Component
{
    /**
     * fields which will replace the regular syntax in where i.e. field = 'value'
     */
    public $fieldFormatting = [
        'string' => '%%%s%%',
        'integer' => '%s',
        'boolean' => '%s',
        'date' => '`%s`%',
    ];

    /**
     * Function which will change controller->request->data array
     *
     * @param $controller
     * @return array
     */
    public function process(&$controller)
    {
        $request_data = $this->prepareFilter($controller);

        $paginate_conditions = [];

        if (count($request_data) > 0) {
            $db = ConnectionManager::get('default');
            $collection = $db->getSchemaCollection();
            $schema = $collection->describe(strtolower($controller->getName()));

            foreach ($request_data as $field => $value) {
                if ($field === 'limit') {
                    continue;
                }
                $column = $schema->getColumn($field);
                if ($column['type'] == 'boolean') {
                    $paginate_conditions[$controller->getName() . '.' . $field] = (bool)$value;
                } elseif ($column['type'] == 'integer') {
                    $paginate_conditions[$controller->getName() . '.' . $field] = (int)$value;
                } elseif ($field !== 'limit') {
                    $paginate_conditions[$controller->getName() . '.' . $field . ' LIKE '] = sprintf($this->fieldFormatting[$column['type']], $value);
                }
            }
        }

        return ['paginate_conditions' => $paginate_conditions, 'request_data' => $request_data];
    }

    /**
     * Function which will take care of the storing the filter data and loading after this from the Session
     *
     * @param $controller
     * @return array
     */
    private function prepareFilter(&$controller)
    {
        $controller_name = $controller->getName();
        $controller_action = 'index'; // $controller->getRequest()->getParam('action')
        $request_data = $controller->getRequest()->getData();
        $session = $controller->getRequest()->getSession();
        if (count($request_data) > 0) {
            foreach ($request_data as $field => $value) {
                if ($value === '') {
                    unset($request_data[$field]);
                }
            }
            $session->write($controller_name . '.' . $controller_action, $request_data);
        }

        return $session->check($controller_name . '.' . $controller_action) ? $session->read($controller_name . '.' . $controller_action) : [];
    }
}
