<?php
/**
 * Implementation of HybridAuth (http://hybridauth.sourceforge.net/) into Yii component.
 *
 * @author V.Zang Loo
 
 example config
 
 'hybridAuth' => array(
    'class' => 'vendor.ivko.yii-hybridAuth.components.CHybridAuth',
    'enabled' => true, // enable or disable this component
    'showError' => true,
    'config' => array(
        "base_url" => "site/endpoint",
        "providers" => array(
            "Google" => array(
                "enabled" => false,
                "keys" => array(
                    "id" => "",
                    "secret" => ""
                ) ,
            ) ,
            "Facebook" => array(
                "enabled" => true,
                "scope" => "read_stream,email,offline_access",
                "keys" => array(
                    "id" => "275907229200799",
                    "secret" => "3d2ed4e22ec4aac3bc97387da9a19f29"
                ) ,
            ) ,
            "Twitter" => array(
                "enabled" => false,
                "keys" => array(
                    "key" => "",
                    "secret" => ""
                )
            ) ,
        ) ,
        "debug_mode" => false,
        "debug_file" => "hybridAuth-debug.txt",
    ) ,
) ,
 
 
example widget:
<?php $this->widget('vendor.ivko.yii-hybridAuth.widgets.SocialLoginButtonWidget', array(
    'enabled'=>Yii::app()->hybridAuth->enabled,
    'providers'=>Yii::app()->hybridAuth->getAllowedProviders(),
    'route'=> 'site/endpoint',
)); ?>



exapmle controller:
public function actionEndpoint(){
    Yii::app()->hybridAuth->endPoint();
}

 */
class CHybridAuth extends CApplicationComponent {
    
    const
        OPENID='OpenID',
        FACEBOOK='Facebook',
        GOOGLE='Google',
        TWITTER='Twitter',
        YAHOO='Yahoo',
        MYSPACE='MySpace',
        LINKED_IN='LinkedIn',
        FOURSQUARE='Foursquare',
        WINDOWS_LIVE='Live',
        AOL='AOL'
    ;
    
    public $config;
    public $showError=false;
    public $enabled=true;
    
    private $_hybridAuth;
    private $_adapter;
    
    private $_libPath;
    private $_errors;
    
    public function init(){
        parent::init();
        
        $this->_libPath = Yii::getPathOfAlias('vendor.hybridauth.hybridauth.hybridauth');
        
        if ($this->config===null) {
            $this->config = require_once($this->_libPath . '/config.php');
        }

        if (!preg_match("/^(http|https):\/\//", $this->config['base_url'])) {
            $this->config['base_url'] = Yii::app()->controller->createAbsoluteUrl($this->config['base_url']);
        }

        require_once($this->_libPath . '/Hybrid/Auth.php');
    }
    
    public function endPoint(){
        require_once($this->_libPath . '/index.php');
    }
    
    public function isAllowedProvider($provider){
        if(isset($this->config['providers'][$provider]['enabled'])){
            return $this->config['providers'][$provider]['enabled'];
        }
        return false;
    }
    
    public function getAllowedProviders(){
        $providers=array();
        if(isset($this->config['providers'])){
            foreach($this->config['providers'] as $provider=>$config){
                if(isset($config['enabled']) && $config['enabled'])
                    $providers[]=$provider;
            }
        }
        return $providers;
    }
    
    /**
     * Hybrid Auth functions
     */
    public function getHybridAuth(){
        if($this->_hybridAuth===null){
            return $this->_hybridAuth = new Hybrid_Auth($this->config);
        }
        return $this->_hybridAuth;
    }
    
    public function getAdapter($provider, $params=array()){
        try{
            if(isset($this->_adapter[$provider])){
                return $this->_adapter[$provider];
            }
            return $this->_adapter[$provider] = $this->getHybridAuth()->authenticate($provider, $params);
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function isConnectedWith($provider){
        try{
            return $this->getHybridAuth()->isConnectedWith($provider);
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function getConnectedProviders(){
        try{
            return $this->getHybridAuth()->getConnectedProviders();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function getSessionData(){
        try{
            return $this->getHybridAuth()->getSessionData();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function restoreSessionData($sessiondata){
        try{
            return $this->getHybridAuth()->restoreSessionData($sessiondata);
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function logoutAllProviders(){
        try{
            return $this->getHybridAuth()->logoutAllProviders();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function redirect($url){
        try{
            return $this->getHybridAuth()->redirect($url);
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function getCurrentUrl(){
        try{
            return $this->getHybridAuth()->getCurrentUrl();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    /**
     * Adapter functions
     */
    public function getAdapterApi($provider){
        try{
            return $this->getAdapter($provider)->api();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function isAdapterUserConnected($provider){
        try{
            return $this->getAdapter($provider)->isUserConnected();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function logoutAdapter($provider){
        try{
            $this->getAdapter($provider)->logout();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function setAdapterUserStatus($provider, $status){
        try{
            $this->getAdapter($provider)->setUserStatus($status);
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function getAdapterUserProfile($provider){
        try{
            return $this->getAdapter($provider)->getUserProfile();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function getAdapterUserContacts($provider){
        try{
            return $this->getAdapter($provider)->getUserContacts();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    public function getAdapterUserActivity($provider, $stream='timeline'){
        try{
            return $this->getAdapter($provider)->getUserActivity($stream);
        }catch(Exception $e){
            $this->_errorMessage($e);
        }       
    }
    
    public function getAdapterAccessToken($provider){
        try{
            return $this->getAdapter($provider)->getAccessToken();
        }catch(Exception $e){
            $this->_errorMessage($e);
        }
    }
    
    private function _errorMessage($e){
        switch($e->getCode()){ 
            case 0 : $error = "Unspecified error."; break;
            case 1 : $error = "Hybriauth configuration error."; break;
            case 2 : $error = "Provider not properly configured."; break;
            case 3 : $error = "Unknown or disabled provider."; break;
            case 4 : $error = "Missing provider application credentials."; break;
            case 5 : $error = "Authentication failed. The user has canceled the authentication or the provider refused the connection."; break;
            case 6 : $error = "User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again."; break;
            case 7 : $error = "User not connected to the provider."; break;
            case 8 : $error = "Provider does not support this feature."; break;
        }
        if($this->showError){
            $error .= "<br /><br /><b>Original error message:</b> " . $e->getMessage(); 
            $error .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>"; 
            echo $error;
        }
        $this->_errors=$error;
    }
    
    public function getErrors(){
        return $this->_errors;
    }
    
}