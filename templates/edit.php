<?php  
  $urlGenerator = \OC::$server->getURLGenerator();
  $nonce = \OC::$server->getContentSecurityPolicyNonceManager()->getNonce();
  $scriptsrc = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/edit.js');
  $scriptbs = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/bootstrap-5.2.3-dist/js/bootstrap.bundle.min.js');
  $stylebs = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/bootstrap-5.2.3-dist/css/bootstrap.min.css');
  $version = \OCP\App::getAppVersion('ctfviewer'); 
?>

<link href="<?php p($stylebs)?>" rel="stylesheet">
<script nonce="<?php p($nonce)?>" src="<?php p($scriptsrc)?>?v=<?php p($version) ?>"></script>
<script nonce="<?php p($nonce)?>" src="<?php p($scriptbs)?>?v=<?php p($version) ?>"></script>


<div>
  <input type="hidden" value="<?php p($_['owner'])?>" id="fOwner" />
  <input type="hidden" value="<?php p($_['file'])?>" id="fFile" />
  <input type="hidden" value="<?php p($_['token'])?>" id="fToken" />
  <input type="hidden" value="<?php p($_['editor'])?>" id="fEditor" />
  <input type="hidden" value="<?php p($_['username'])?>" id="fUsername" />
</div>
<div class="container">
    <div class="row">
        <div id="ctfLoading" class="mt-5">
            <div class="d-flex align-items-center">
                <span>Lade Aufgabenliste...</span>
                <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="ctfError" class="mt-5" style="display:none;">
            <div class="alert alert-danger" role="alert">
                <div id="ctfErrorText">Unknown Error</div>
            </div>
        </div>
    </div>
    
        <div id="ctfReady" class="mt-3" style="display:none;">
            
            <!-- Title -->
            <div class="row">
                <div>
                    <p class="fs-2" id="ctfTitle"></p>
                </div>
            </div>

            <!-- Summary -->
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title <?php if ($_['editor'] == "NO") { ?>icon-tag <?php } ?>">Mandanten</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Bearbeitungsstand</h6>
                            <p class="card-text">
                                <div class="fw-light">
                                    <div class="float-start" style="height:25px;">
                                        <p>Letzte Bearbeitung:</p>
                                    </div>
                                    <div class="float-end" style="height:25px;">
                                        <p id="lastModifiedMandant"></p>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="fw-light">
                                    <div class="float-start" style="height:25px;">
                                        <p>von</p>
                                    </div>
                                    <div class="float-end" style="height:25px;">
                                        <p id="lastEditorMandant"></p>
                                    </div>
                                </div>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title <?php if ($_['editor'] == "YES") { ?>icon-tag <?php } ?>">Kanzlei</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Bearbeitunsstand</h6>
                            <p class="card-text">
                                <div class="fw-light">
                                    <div class="float-start" style="height:25px;">
                                        <p>Letzte Bearbeitung:</p>
                                    </div>
                                    <div class="float-end" style="height:25px;">
                                        <p id="lastModifiedKanzlei"></p>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="fw-light">
                                    <div class="float-start" style="height:25px;">
                                        <p>von</p>
                                    </div>
                                    <div class="float-end" style="height:25px;">
                                        <p id="lastEditorKanzlei"></p>
                                    </div>
                                </div>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Elements -->
            <div class="row mt-5" style="margin-left:3px;margin-right:3px;">
                <div class="card" style="padding-top:15px;padding-bottom:15px;">
            <div>
                <div class="card">
                    <div class="card-header">
                        Offen
                        <span class="badge rounded-pill text-bg-danger" id="ctfCountOpen">N/A</span>
                        <?php if ($_['editor'] == "YES") { ?>
                        <div class="float-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#newModal">
                                Neu
                            </button>
                        </div>
                        <?php } ?>
                    </div>
                <div class="card-body">
                    <div id="tasksOpen"></div>
                </div>
            </div>
            <div class="mt-4">
                <div class="card">
                    <div class="card-header">
                        Von Mandanten erledigt
                        <span class="badge rounded-pill text-bg-success" id="ctfCountReady">N/A</span>
                    </div>
                <div class="card-body">
                    <div id="tasksReady"></div>
                </div>
            </div>
            <div class="mt-4">
                <div class="card">
                    <div class="card-header">
                        Archivierte
                        <span class="badge rounded-pill text-bg-secondary" id="ctfCountDone">N/A</span>
                        <div class="float-end">
                            <a class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" href="#collapseFinished" role="button" aria-expanded="false" aria-controls="collapseFinished">
                            <div class="icon-toggle"></div>
                            </a>
                        </div>
                    </div>
                    <div class="collapse" id="collapseFinished">
                        <div class="card-body">
                            <div id="tasksDone"></div>
                        </div>
                    </div>
                </div>
            </div>
            </div>          
        </div>
        </div>

        <!-- Footer -->
        <div class="row">
        <div class="mt-4">
            <div class="float-end" style="margin-right:-35px;">
                <button type="button" class="btn btn-secondary" id="btnCloseForm">
                    Fertig
                </button>
                <button class="btn btn-primary" type="button" disabled id="btnCloseFormSave" style="display:none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Speichern...
                </button>
            </div>
        </div>
        <div>

    </div>
</div>

<!-- Modal to add new task -->
<div class="modal fade" id="newModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Neue Aufgabe</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1">Beschreibung</span>
            <input type="text" class="form-control" placeholder="" aria-label="formTitle" id="formTitle" aria-describedby="basic-addon1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
        <button type="button" class="btn btn-primary" id="modalSaveBtn">Speichern</button>
      </div>
    </div>
  </div>
</div>

<!-- Template Tasks -->
<template id="taskTemplate">
    <div style="margin-top:5px;">
        <div class="card" style="padding: 15px 15px 0px 15px;">
            <div class="cleanfix">
                <div class="float-start">
                    <p class="fs-6 ctfTaskTitle"></p>
                </div>
                <div class="float-end" style="margin-top:-6px;">
                   <button class="btn btn-outline-secondary btn-sm ctfBtnDone" data-ctfaction="done">
                        <div class="icon-checkmark"></div>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ctfBtnRemove" data-ctfaction="remove">
                        <div class="icon-delete"></div>
                    </button>
                </div>
            </div> 
        </div>
    </div>
</template>


<!-- Template No Tasks -->
<template id="notaskTemplate">
    <div style="margin-top:5px;">
        <div style="text-align:center;">
            <p class="fs-6">Alles erledigt</p> 
        </div>
    </div>
</template>