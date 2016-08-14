<?php
namespace CakeTools\Controller;

use CakeTools\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * Users Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @property \CakephpCaptcha\Controller\Component\CaptchaComponent $Captcha
 */
class AdminsController extends AppController
{
    
    
    /** 
     * editStatus method
     * 
     * @param string $alias
     * @param string $id
     * @param int $status
     * @throws Exception
     */
    public function editStatus($alias = null, $id = null, $status = null)
    {
        $model = TableRegistry::get($alias);

        if (!$model->exists(['id' => $id]) || !$model->hasField('status')) {
            throw new Exception('Record does not exist or Model does not have status field');
        }

        $className = 'Const' . $alias . 'Status';
        $exists = false;
        if (class_exists($className)) {
            $statusList = getClassConstants($className);
        } else {
            $statusList = getClassConstants('ConstGeneralStatus');
        }

        if (in_array($status, $statusList)) {
            $data = [
                'id' => $id,
                'status' => $status
            ];

            if ($model->saveOne($data, ['check' => false])) {
                $this->Flash->success(__('Status has been changed'));
            } else {
                $this->Flash->error(__('Unknown error occurred'));
            }
        } else {
            $this->Flash->error(__('Invalid status value'));
        }

        $this->redirect($this->referer());
    }

    
    /** 
     * position method
     * 
     * @param string $alias
     * @param string $id
     * @param string $type
     */
    public function position($alias = null, $id = null, $type)
    {
        $model = TableRegistry::get($alias);
        
        if (!$model->exists(['id' => $id])) {
            throw new Exception('Record does not exist');
        }
        
        if ($model->move($id, $type)) {
            $this->Flash->success(__('Position has been changed'));
        } else {
            $this->Flash->error(__('Position can not be changed'));
        }
        
        $this->redirect($this->referer());
    }
    
    
    /** 
     * delete method
     * 
     * @param string $alias
     * @param int $id
     */
    public function delete($alias = null, $id = null)
    {
        $model = TableRegistry::get($alias);
        $singularAlias = Inflector::singularize($alias);
        
        if (!$model->exists(['id' => $id])) {
            throw new Exception('Record does not exist');
        }
        
        /* @var $conn \Cake\Database\Connection  */
        $conn = ConnectionManager::get('default');
        if ($model->hasField('deleted')) {
            $data = [
                'id' => $id,
                'deleted' => CURRENT_TIME
            ];

            $conn->begin();
            if ($model->saveOne($data, ['validate' => false])) {
                $conn->commit();
                $this->Flash->success(__('{0} has been deleted', $singularAlias));
            } else {
                $conn->rollback();
                $this->Flash->error(__('Unknown error occurred'));
            }
        } else {
            $conn->begin();
            if ($model->deleteAll(['id' => $id])) {
                $conn->commit();
                $this->Flash->success(__('{0} has been deleted', $singularAlias));
            } else {
                $conn->rollback();
                $this->Flash->error(__('{0} can not be deleted', $singularAlias));
            }
        }

        return $this->redirect($this->referer());
    }
    
    
    
    
    
    
    
}