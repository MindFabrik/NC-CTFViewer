<?php
  /** @var array $_ */
  script('ctfviewer', 'script');
  style('ctfviewer', 'style');
  
  $urlGenerator = \OC::$server->getURLGenerator();
  $nonce = \OC::$server->getContentSecurityPolicyNonceManager()->getNonce();
  $scriptsrc = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/settings-admin.js');
  $version = \OCP\App::getAppVersion('ctfviewer'); 
  //$data = $_['data'];
?>
<script nonce="<?php p($nonce)?>" src="<?php p($scriptsrc)?>?v=<?php p($version) ?>"></script>
<style>
hr { 
    display: block;
    margin-top: 0.5em;
    margin-bottom: 0.5em;
    margin-left: auto;
    margin-right: auto;
    border-style: inset;
    border-width: 1px;
} 
</style>


<div id="section-ctfv" class="section">

        <h2>Einstellungen der CTF Viewer</h2>
     
</div>
