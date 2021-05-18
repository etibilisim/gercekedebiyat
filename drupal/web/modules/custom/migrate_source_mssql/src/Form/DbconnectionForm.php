<?php
/**
 * @file
 * Contains \Drupal\resume\Form\ResumeForm.
 */
namespace Drupal\migrate_source_mssql\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
class DbconnectionForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'mssql_db_connection_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['servername'] = array(
            '#type' => 'textfield',
            '#title' => t('Server Name:'),
            '#required' => TRUE,
            '#default_value'=> \Drupal::state()->get('servername')
        );
        $form['uid'] = array(
            '#type' => 'textfield',
            '#title' => t('User ID:'),
            '#required' => TRUE,
            '#default_value'=> \Drupal::state()->get('username')
        );
        $form['password'] = array(
            '#type' => 'password',
            '#title' => t('Password'),
            '#required' => TRUE,
            '#default_value'=> \Drupal::state()->get('password')
        );
        $form['database'] = array(
            '#type' => 'tel',
            '#title' => t('Database Name'),
            '#required' => TRUE,
            '#default_value'=> \Drupal::state()->get('database')
        );
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Test MSSQL Connection'),
            '#button_type' => 'primary',
        );
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
      /*  if (strlen($form_state->getValue('candidate_number')) < 10) {
            $form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
        }*/
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
       $conn_val = array();
       foreach ($form_state->getValues() as $key => $value) {
            $conn_val[$key] = $value;
        }
        \Drupal::state()->set('servername', $conn_val['servername']);
        \Drupal::state()->set('username', $conn_val['uid']);
        \Drupal::state()->set('password', $conn_val['password']);
        \Drupal::state()->set('database', $conn_val['database']);
        $serverName = \Drupal::state()->get('servername'); //serverName\instanceName
        $username = \Drupal::state()->get('username'); //serverName\instanceName
        $password = \Drupal::state()->get('password'); //serverName\instanceName
        $database = \Drupal::state()->get('database'); //serverName\instanceName
        $connectionInfo = array("UID"=> $username, "PWD"=> $password,"Database"=> $database);
        $conn = sqlsrv_connect( $serverName, $connectionInfo);
        if( $conn ) {
            drupal_set_message("Bravo ! Connection established. Ready to Fly.");
        }else{
            drupal_set_message("Oops! Check your MSSQL drivers properly configured ! Connection could not be established.");
            drupal_set_message( print_r( sqlsrv_errors(), true));
        }
    }
}