<?php  
  $urlGenerator = \OC::$server->getURLGenerator();
  $nonce = \OC::$server->getContentSecurityPolicyNonceManager()->getNonce();
  $scriptjquery = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/jquery/jquery-3.6.3.min.js');
  $scriptsrc = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/edit.js');
  $scriptbs = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/bootstrap-5.2.3-dist/js/bootstrap.bundle.min.js');
  $stylebs = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/bootstrap-5.2.3-dist/css/bootstrap.min.css');
  $version = \OC::$server->getAppManager()->getAppVersion('ctfviewer');
  $scriptbsac = \OC::$server->getURLGenerator()->linkTo('ctfviewer', 'js/bootstrap-autocomplete/js/bootstrap-autocomplete.min.js');
?>

<link href="<?php p($stylebs)?>" rel="stylesheet">
<script nonce="<?php p($nonce)?>" src="<?php p($scriptjquery)?>?v=<?php p($version) ?>"></script>
<script nonce="<?php p($nonce)?>" src="<?php p($scriptbs)?>?v=<?php p($version) ?>"></script>
<script nonce="<?php p($nonce)?>" src="<?php p($scriptbsac)?>?v=<?php p($version) ?>"></script>
<script nonce="<?php p($nonce)?>" src="<?php p($scriptsrc)?>?v=<?php p($version) ?>"></script>

<style>
    .ctfbg {
        background: white;
        border-radius: 10px;
        padding: 10px;
        overflow-y: auto;
        overflow-x: hidden;
        max-height: 100vh;
    }

    .modal-backdrop {
        display: none;
        z-index: 1101 !important;
        
    }

    .modal-content {
        margin: 2px auto;
        z-index: 1101 !important;
    }
    .modal {
        background: rgba(100,100,100,.5);
    }

    input[id^='oc-dialog-'] {
        width: 100%;
    }

    .ctfHistory {
        white-space: pre;
        color: gray;
    }
</style>

<div>
  <input type="hidden" value="<?php p($_['owner'])?>" id="fOwner" />
  <input type="hidden" value="<?php p($_['file'])?>" id="fFile" />
  <input type="hidden" value="<?php p($_['token'])?>" id="fToken" />
  <input type="hidden" value="<?php p($_['editor'])?>" id="fEditor" />
  <input type="hidden" value="<?php p($_['username'])?>" id="fUsername" />
  <input type="hidden" value="<?php p($_['dateplaceholder'])?>" id="fDatePlaceholder" />
</div>
<div class="container ctfbg">
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
                    <div class="float-start">
                        <p class="fs-2" id="ctfTitle"></p>
                    </div>
                    <?php if ($_['editor'] == "YES") { ?> 
                    <div class="float-end">
                        <button type="button" class="btn btn-light btn-sm" id="btnCtfTitleEdit" data-bs-toggle="modal" data-bs-target="#editModal">
                            <div class="icon-comment">
                            </div>    
                        </button>
                    </div> 
                    <?php } ?>
                </div>
            </div>

            <!-- Summary -->
            <div class="row">
                <div class="col-6">
                    <div class="card h-100">
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
                                 <div class="clearfix"></div>
                                <div class="mt-3">
                                    <small style="color:gray">
                                        <p id="mandantenNotificationText" style="margin-bottom: 0px;"></p>
                                        <?php if ($_['editor'] == "NO") { ?>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#notificationModal">
                                            E-Mail Benachrichtigungen verwalten
                                        </a>
                                    <?php } ?>
                                    </small>                                         
                                </div>                                
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title <?php if ($_['editor'] == "YES") { ?>icon-tag <?php } ?>">Steuerkanzlei</h5>
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
                         
                                <div class="clearfix"></div>
                                <div class="mt-3">
                                    <?php if ($_['editor'] == "YES") { ?>
                                    <small style="color:gray">
                                        <p id="kanzleiNotificationText" style="margin-bottom: 0px;"></p>                                                                   
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#notificationModal">
                                        E-Mail Benachrichtigungen verwalten
                                    </a>
                                    </small>     
                                    <?php } ?>
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
                        <div class="float-end">
                            <a class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" href="#collapseDone" role="button" aria-expanded="false" aria-controls="collapseDone">
                            <div class="icon-toggle"></div>
                            </a>
                        </div>
                    </div>
                <div class="collapse" id="collapseDone">
                <div class="card-body">
                    <div id="tasksReady"></div>
                </div>
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
<div class="modal fade" id="newModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Neue Aufgabe</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      
      <div class="mt-1 mb-2">
            <div class="btn-group" role="group" aria-label="">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" value="Betrieb" checked>
                <label class="btn btn-outline-secondary" for="btnradio1">Betrieb</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off" value="Mitarbeiter">
                <label class="btn btn-outline-secondary" for="btnradio2">Mitarbeiter</label>
            </div>
        </div>

        <div class="card" style="padding: 15px 15px 0px 15px">
        <div class="input-group mb-3">
            <span class="input-group-text w-25" id="basic-addon1">Beschreibung</span>
            <input type="text" class="form-control" placeholder="" autocomplete="off" aria-label="formTitle" id="formTitle" aria-describedby="basic-addon1">
        </div>
        <div class="input-group mb-3">
            <span class="input-group-text w-25" id="basic-addon2">Datum</span>
            <input type="text" class="form-control" placeholder="" autocomplete="off" aria-label="formTitle" id="formDate" aria-describedby="basic-addon2" value="<?php p($_['dateplaceholder'])?>">
        </div>
        </div>
       
        <div id="ctfMitarbeiterFields" class="card mt-2" style="padding: 15px 15px 0px 15px;display:none;">
            <div class="input-group mb-3">
                <span class="input-group-text w-25" id="basic-addon3">Pers.Nr.</span>
                <input type="text" class="form-control" placeholder="" autocomplete="off" aria-label="formTitle" id="formPN" aria-describedby="basic-addon3">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text w-25" id="basic-addon4">MA Name</span>
                <input type="text" class="form-control" placeholder="" autocomplete="off" aria-label="formTitle" id="formName" aria-describedby="basic-addon4">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text w-25" id="basic-addon5">MA Vorname</span>
                <input type="text" class="form-control" placeholder="" autocomplete="off" aria-label="formTitle" id="formVorname" aria-describedby="basic-addon5">
            </div>
        </div>
        <div class="mt-2 card"  style="padding: 15px 15px 0px 15px;">
            <div class="input-group mb-3">
                <span class="input-group-text w-25" id="basic-addon6">Bemerkung</span>
                <input type="text" class="form-control" placeholder="" autocomplete="off" aria-label="formTitle" id="formDescription" aria-describedby="basic-addon6">
            </div>            
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
        <button type="button" class="btn btn-primary" id="modalSaveBtn">Speichern</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Tasktemplate -->
<div class="modal" tabindex="-1" id="editModal" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Aufgabenliste</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="mt-2 card"  style="padding: 15px 15px 0px 15px;">
            <div class="input-group mb-3">
                <span class="input-group-text w-25" id="basic-addon7">Titel</span>
                <input type="text" class="form-control" placeholder="" list="list-taskname" autocomplete="off" aria-label="formMDTitle" id="formMDTitle" aria-describedby="basic-addon7">
                <datalist id="list-taskname">
                    <option>Fehlende Lohn-Abrechnungsinformationen</option>
                    <option>Buchführung</option>
                    <option>Jahresabschluss</option>
                </datalist>
            </div>            
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
        <button type="button" class="btn btn-primary" id="btnCftSaveMetadata">Speichern</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Description -->
<div class="modal" tabindex="-1" id="descModal" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Bemerkung ändern/hinzufügen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <input type="hidden" id="modalDataDescPos"/>      
      <div class="modal-body">
      <div class="mt-2 card"  style="padding: 15px 15px 0px 15px;">
            <div class="input-group mb-3">
                <span class="input-group-text w-25" id="basic-addon8">Bemerkung</span>
                <input type="text" class="form-control" placeholder="" autocomplete="off" aria-label="formDescChange" id="formDescChange" aria-describedby="basic-addon8">
            </div>            
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
        <button type="button" class="btn btn-primary" id="btnCftSaveDesc">Speichern</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Enable Email notification -->
<div class="modal fade" data-bs-backdrop="static" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="notificationModalLabel">E-Mail Benachrichtigung</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mt-2 card" style="padding: 15px 15px 0px 15px;">
            <div class="input-group mb-3">
                <p>Tragen Sie bitte Ihre E-Mail Adresse ein, wenn Sie über eine neue Aufgabe informiert werden wollen. Lassen Sie dieses Feld leer, wenn Sie keine Benachrichtigungen erhalten möchten.</p>
                <span class="input-group-text w-25" id="basic-addon9">E-Mail</span>
                <input type="email" class="form-control" placeholder="" autocomplete="off" aria-label="formEMailNotification" id="formEMailNotification" aria-describedby="basic-addon9">               
            </div>            
            <div class="mb-2">
                <a href="#" id="linkDeactiveEMail">E-Mail Benachrichtigungen deaktivieren</a>
            </div>
        </div>      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
        <button type="button" class="btn btn-primary" id="btnCftSaveNotification">Speichern</button>
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
                    <p class="fs-5 ctfTaskTitle"></p>
                    <div style="margin-top:-10px;">
                        <span class="badge rounded-pill text-bg-secondary mb-2 ctfTaskType">Unknown</span>
                        <span class="ctfTaskDate"></span>
                    </div>                    
                </div>
                <div class="float-end" style="margin-top:-6px;">
                   <button class="btn btn-outline-secondary btn-sm ctfBtnDone" data-ctfaction="done">
                        <div class="icon-checkmark"></div>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ctfBtnEdit" data-ctfaction="edit">
                        <div class="icon-rename"></div>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ctfBtnUndo" data-ctfaction="undo">
                        <div class="icon-history"></div>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ctfBtnRemove" data-ctfaction="remove">
                        <div class="icon-delete"></div>
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <p class="ctfTaskDescriptionTitle"></p>
                <p class="fw-light ctfTaskDescription" style="margin-top:-15px"></p>
                <p class="ctfTaskDescriptionTitle2"></p>
                <p class="fw-light ctfTaskDescription2"style="margin-top:-15px"></p>
            </div>
            <hr/>
            <div class="mb-1">
                <small>
                    <strong><p class="ctfHistoryTitle"></p></strong>
                    <p class="ctfHistory"></p>
                </small>
                <small style="color:lightgray;">
                    <p class="ctfMetadata"></p>
                </small>
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