/*
    The edit.js javascript will be loaded from the edit.php template
    and apply the full logic of displaying and modifying of a CTF file.
    Every change is saved immediately. A queue is used for this purpose.

    The context of a CTF file is requested via the json/getctf call. To save
    the file, the script will use the json/setctf call.

*/


// *******************************************
// -------------  PAGE  READY ----------------
// Set event listener and request data from 
// server (getctf function)
// *******************************************
document.addEventListener('DOMContentLoaded', function () {

    // Add Event Listener for Buttons 
    $("#modalSaveBtn").click(function() {
        saveModalData();
    });
    $("#btnCftSaveMetadata").click(function() {
        saveMetadata();
    });
    $("#btnCftSaveDesc").click(function() {
        ctfBtnHandlerEditSave();
    });
    $("#btnCftSaveNotification").click(function() {
        ctfBtnHandlerEMailNotificaiton();
    });
    $("#btnCloseForm").click(function() {
        closeViewer();
    });    
    $('input[type=radio][name=btnradio]').change(function() {
       if (this.value === "Mitarbeiter") {
        $("#ctfMitarbeiterFields").fadeIn(400, function() {
            $("#ctfMitarbeiterFields").show();
          });
       }
       else {
        $("#ctfMitarbeiterFields").fadeOut(400, function() {
            $("#ctfMitarbeiterFields").hide();
          });
       }
    });
    $("#linkDeactiveEMail").click(function() {
        $("#formEMailNotification").val("");
    });
    const notificationModal = document.getElementById('notificationModal')
    notificationModal .addEventListener('shown.bs.modal', () => {
        let editor = $("#fEditor").val();        
        let json = JSON.parse(JSON.stringify(serverData));
        if (editor == "YES") {
            $("#formEMailNotification").val(json.notificationKanzlei);
        }
        else {
            $("#formEMailNotification").val(json.notificationMandant);
        }
    })

    // Add AutoComplete
    document.addEventListener('DOMContentLoaded', e => {
        $('#formMDTitle').autocomplete()
    }, false);

    // Load CTF document
    getctf();
});

// Global variables
var serverData;


// *******************************************
// Save Function when pressing 'Save' in modal
// *******************************************
function saveModalData() {

    const currentDate = new Date();
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeStr = currentDate.toLocaleDateString('de-DE', options);

    // Create new tasklist object
    if (document.querySelector('input[name="btnradio"]:checked').value == "Mitarbeiter") {
        let obj = {
            "taskTitle": $("#formTitle").val(),
            "status": 0,
            "id": (+new Date).toString(36).slice(-10),
            "addedBy": $("#fUsername").val(),
            "addedAt": timeStr,
            "taskType": "Mitarbeiter",
            "taskDate": $("#formDate").val(),
            "taskDesciption": $("#formDescription").val(),
            "taskDesciption2": "",
            "maPN":$("#formPN").val(),
            "maName":$("#formName").val(),
            "maVorname":$("#formVorname").val(),
        };
        serverData.taskList.push(obj);
    }
    else if (document.querySelector('input[name="btnradio"]:checked').value == "Betrieb") {
        let obj = {
            "taskTitle": $("#formTitle").val(),
            "status": 0,
            "id": (+new Date).toString(36).slice(-10),
            "addedBy": $("#fUsername").val(),
            "addedAt": timeStr,
            "taskType": "Betrieb",
            "taskDate": $("#formDate").val(),
            "taskDesciption": $("#formDescription").val(),
            "taskDesciption2": "",
            "maPN":"",
            "maName":"",
            "maVorname":"",
        };
        serverData.taskList.push(obj);
    }
    

    // Update Modifier Information
    updateModifierData();

    // Populate Data and clean up modal
    populateData();
    setctf();
    ctfSendNotificationNewTask($("#formTitle").val());
    cleanModalData();
    $('#newModal').modal('hide');
}

// *******************************************
// Updating Modifier Data (after a change)
// *******************************************
function updateModifierData() {

    const currentDate = new Date();
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeStr = currentDate.toLocaleDateString('de-DE', options);

    // Update Kanzlei or Mandanten Modify Information
    if ($("#fEditor").val() == "YES") {
        serverData.lastModifiedKanzlei = timeStr;
        serverData.lastEditorKanzlei =  $("#fUsername").val();
    } else {
        serverData.lastModifiedMandant = timeStr;
        serverData.lastEditorMandant =  $("#fUsername").val();
    }

}

// ************************************************
// Get Modifier Information (for comment tracking)
// ************************************************
function getModifierInformation(input) {

    const currentDate = new Date();
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeStr = currentDate.toLocaleDateString('de-DE', options);

    var retVal = "";
    var sUsername = $("#fUsername").val();

    if ($("#fEditor").val() == "YES") {
       retVal = "Kanzlei (" + sUsername + "), " + timeStr + ": " + input;
    } else {
       retVal = "Mandant (" + sUsername + "), " + timeStr + ": " + input;
    }

    return retVal;

}

// *******************************************
// Function to reset all modal data
// *******************************************
function cleanModalData() {
    $("#formTitle").val("");
    $("#formDate").val($("#fDatePlaceholder").val());
    $("#formDescription").val("");
    $("#formPN").val("");
    $("#formName").val("");
    $("#formVorname").val("");
    $("#btnradio1").prop("checked", true);
    $("#ctfMitarbeiterFields").hide();
}

// *******************************************
// Save Metadata
// *******************************************
function saveMetadata() {
    serverData.title = $("#formMDTitle").val();
    populateData();
    setctf();
    $('#editModal').modal('hide');
}

// *******************************************
// Function to request data of selected CTF
// (executed during page load)
// *******************************************
function getctf() {

    var fFile = $("#fFile").val();
    var fOwner = $("#fOwner").val();
    $.post(
		OC.generateUrl('/apps/ctfviewer/json/getctf'),
		{
			owner: fOwner,
            file: fFile,
		}
	).done(function (data) {
        $("#ctfLoading").hide();
        if (data.result == "ERROR") {
            $("#ctfErrorText").html(data.error)
            $("#ctfError").show();
        }
        try {
            serverData = jQuery.parseJSON(data.json);
            populateData();
        } catch (error) {
            console.error(error);
            $("#ctfErrorText").html(error)
            $("#ctfError").show();
          }
        
	})
	.fail(function (data) {
        $("#ctfLoading").hide();
        $("#ctfErrorText").html(data)
        $("#ctfError").show();
	});



}

// *******************************************
// Start Saving activity...
// Add serverData to queue and proceed when
// no other save process is active
// *******************************************
var saveInProgress = false;
function setctf() {

    console.info("Queue CTF Save action...");

    let sendMessage = (message) => {
     
        queueMicrotask(() => {
            
            // Check if Saving already in progress, then wait...
            var interval = setInterval(function() {
                console.log('Interval Running');
                if( saveInProgress === true){
                    // Wait...
                }
                else {          
                    // Prepare Saving...     
                    clearInterval(interval);
                    saveInProgress = true;                    
                    $("#btnCloseForm").hide();
                    $("#btnCloseFormSave").show();

                    // Start Saving...
                    var fFile = $("#fFile").val();
                    var fOwner = $("#fOwner").val();

                    $.post(
		                OC.generateUrl('/apps/ctfviewer/json/setctf'),
		                {
			                owner: fOwner,
                            file: fFile,
                            content: JSON.stringify(message)
		                }
	                ).done(function (data) {                        
                        if (data.result == "OK") {           
                            saveDone();
                        }
                        else {
                            // Save failed with error
                            saveDone();
                            console.error("CTF Saving failed: " + data);
                        }       
	                })
	                .fail(function (data) {
                        // Report Error
                        saveDone();
                        console.error("CTF Saving failed: " + data);
	                });
                }
            }, 200);            
        });
      }
    
    // Add data to save queue...
    sendMessage(serverData);
       
}
// Helper function for setctf() to finish save process
function saveDone() {
    // Finish Saving and exit queue
    $("#btnCloseForm").show();
    $("#btnCloseFormSave").hide();
    saveInProgress = false;
}

// *******************************************
// Function to Close Viewer and return to NC
// *******************************************
function closeViewer() {
    window.history.back();
}

// *******************************************
// Populated data from serverData variable
// *******************************************
function populateData() {
    var json = serverData;
    
    // Build Header Information
    $("#ctfTitle").text(json.title);
    $("#formMDTitle").val(json.title);
    if (json.lastModifiedMandant == null) {
        $("#lastModifiedMandant").html("N/A");
    }
    else {
        $("#lastModifiedMandant").html(json.lastModifiedMandant);
    }
    if (json.lastModifiedKanzlei == null) {
        $("#lastModifiedKanzlei").html("N/A");
    }
    else {
        $("#lastModifiedKanzlei").html(json.lastModifiedKanzlei);
    }
    if (json.lastModifiedMandant == null) {
        $("#lastEditorMandant").html("N/A");
    }
    else {
        $("#lastEditorMandant").html(json.lastEditorMandant);
    }
    if (json.lastModifiedKanzlei == null) {
        $("#lastEditorKanzlei").html("N/A");
    }
    else {
        $("#lastEditorKanzlei").html(json.lastEditorKanzlei);
    }

    // EMail Notification
    var kNotText = "<div class='icon-alert-outline' style='background-position: left; padding-left:20px;'>Keine E-Mail Benachrichtigung aktiv.</div>"
    if (json.notificationKanzlei) {
        kNotText = "Benachrichtigungen werden an " + json.notificationKanzlei + " versendet."
    }
    $("#kanzleiNotificationText").html(kNotText)
    var mNotText = "<div class='icon-alert-outline' style='background-position: left; padding-left:20px;'>Keine E-Mail Benachrichtigung aktiv.</div>"
    if (json.notificationMandant) {
        mNotText = "Benachrichtigungen werden an " + json.notificationMandant + " versendet."
    }
    $("#mandantenNotificationText").html(mNotText)

    // Prep Open Tasks
    var countOpen = 0;
    var countReady = 0;
    var countDone = 0;
    document.querySelector('#tasksOpen').innerHTML = "";
    document.querySelector('#tasksReady').innerHTML = "";
    document.querySelector('#tasksDone').innerHTML = "";
    
    json.taskList.forEach(element => {

        // Load template
        var t = document.querySelector('#taskTemplate');
        t.content.querySelector('.ctfTaskTitle').textContent = element.taskTitle;
        t.content.querySelector('.ctfTaskType').textContent = element.taskType + "-Unterlagen"

        // Build display text
        var formText = "";
        if (element.taskType === "Mitarbeiter") {
            formText = formText + "für ";
        }
        if (element.maName != "") {
            formText = formText + element.maName;
        }
        if (element.maVorname != "") {
            formText = formText + ", " + element.maVorname;
        }
        if (element.maPN != "") {
            formText = formText + " (" + element.maPN + ")";
        }
        if (element.taskDate != "") {
            formText = formText + " aus " + element.taskDate;
        }
        t.content.querySelector('.ctfTaskDate').textContent = formText;

      
       // Add Description        
       const descKanzlei = element.taskDesciption;
       const descMandant = element.taskDesciption2;       
       if (descKanzlei) {
         t.content.querySelector('.ctfTaskDescriptionTitle').textContent = "Bemerkung Steuerberater";
         t.content.querySelector('.ctfTaskDescription').textContent = descKanzlei
       }
       else {
         t.content.querySelector('.ctfTaskDescriptionTitle').textContent = "";
         t.content.querySelector('.ctfTaskDescription').textContent = "";
       }
       if (descMandant) {
         t.content.querySelector('.ctfTaskDescriptionTitle2').textContent = "Bemerkung Mandant";         
         t.content.querySelector('.ctfTaskDescription2').textContent = descMandant;     
       }
       else {
        t.content.querySelector('.ctfTaskDescriptionTitle2').textContent = "";         
        t.content.querySelector('.ctfTaskDescription2').textContent = "";    
       }

        // Add Parameter
        t.content.querySelector('.ctfBtnDone').dataset.taskid = element.id;
        t.content.querySelector('.ctfBtnUndo').dataset.taskid = element.id;
        t.content.querySelector('.ctfBtnRemove').dataset.taskid = element.id;
        t.content.querySelector('.ctfBtnEdit').dataset.taskid = element.id;

         // Output history
         if (element.history) {
            t.content.querySelector('.ctfHistoryTitle').textContent = "Historie:";
            t.content.querySelector('.ctfHistory').textContent = element.history.join("\r\n");
         }
         else {
            t.content.querySelector('.ctfHistoryTitle').textContent = "";
            t.content.querySelector('.ctfHistory').textContent = "";
         }
         
        
        if (element.status == 0) {
            countOpen = countOpen + 1;
            t.content.querySelector('.ctfMetadata').textContent = "Angefordert am " + element.addedAt + " von " + element.addedBy + ".";
            var clone = document.importNode(t.content, true); 
            document.querySelector('#tasksOpen').appendChild(clone);
        }
        else if (element.status == 1) {
            countReady = countReady + 1;
            t.content.querySelector('.ctfMetadata').textContent = "Angefordert am " + element.addedAt + " von " + element.addedBy + ". Eingereicht am " + element.doneAt + " von " + element.doneBy + ".";
            var clone = document.importNode(t.content, true); 
            document.querySelector('#tasksReady').appendChild(clone);
        }
        else if (element.status == 2) {
            countDone = countDone + 1;
            t.content.querySelector('.ctfMetadata').textContent = "Angefordert am " + element.addedAt + " von " + element.addedBy + ". Eingereicht am " + element.doneAt + " von " + element.doneBy + ". Archiviert am " + element.archivedAt + " von " + element.archivedBy + ".";
            var clone = document.importNode(t.content, true); 
            document.querySelector('#tasksDone').appendChild(clone);
        }

       


    });

    // Add NoTasks template when needed
    var t = document.querySelector('#notaskTemplate');
    if (countOpen == 0) {
        var clone = document.importNode(t.content, true); 
        document.querySelector('#tasksOpen').appendChild(clone);
    }
    if (countReady == 0) {
        var clone = document.importNode(t.content, true); 
        document.querySelector('#tasksReady').appendChild(clone);
    }
    if (countDone == 0) {
        var clone = document.importNode(t.content, true); 
        document.querySelector('#tasksDone').appendChild(clone);
    }

    // Update counts
    $("#ctfCountOpen").text(countOpen);    
    $("#ctfCountReady").text(countReady);    
    $("#ctfCountDone").text(countDone);    

    // Update eventhandler: Add e.g. done, remove handler
    const buttons = document.querySelectorAll("[data-ctfaction]")
    buttons.forEach(button => {
        button.addEventListener("click", () => {
            if (button.dataset.ctfaction == "done") {
                ctfBtnHandlerDone(button.dataset.taskid);
            }
            else if (button.dataset.ctfaction == "remove") {
                ctfBtnHandlerRemove(button.dataset.taskid);
            }
            else if (button.dataset.ctfaction == "undo") {
                ctfBtnHandlerUndo(button.dataset.taskid);
            }
            else if (button.dataset.ctfaction == "edit") {
                ctfBtnHandlerEdit(button.dataset.taskid);
            }
            
    })
})
    applyViewFilter();
    $("#ctfReady").show();
}

// *******************************************
// Hide elements based on role
// *******************************************
function applyViewFilter() {

    // Disable move to next for all in archived area
    $(".ctfBtnDone","#tasksDone").css("display","none");
    $(".ctfBtnUndo","#tasksOpen").css("display","none");
    $(".ctfBtnEdit","#tasksDone").css("display","none");

    // Hide elements for Mandanten
    if ($("#fEditor").val() != "YES") { 
        $(".ctfBtnRemove","#tasksOpen").css("display","none");
        $(".ctfBtnRemove","#tasksReady").css("display","none");
        $(".ctfBtnRemove","#tasksDone").css("display","none");
        $(".ctfBtnDone","#tasksReady").css("display","none");
        $(".ctfBtnUndo","#tasksDone").css("display","none");
        $(".ctfBtnEdit","#tasksReady").css("display","none");    
    }
    else {
        $("#collapseDone").collapse("show");
    }

}

// **************************************
// Handler to mark task as done in list
// **************************************
function ctfBtnHandlerDone(taskId) {

    let json = JSON.parse(JSON.stringify(serverData));

    // Find pos in tasklist
    var index = json.taskList.findIndex(function(item, i){
        return item.id === taskId;
    });

    // Modify
    if (json.taskList[index].status < 2) {
        json.taskList[index].status = json.taskList[index].status + 1;
    }

    // Set Metadata
    const currentDate = new Date();
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeStr = currentDate.toLocaleDateString('de-DE', options);

    let formText = "";
    if (json.taskList[index].status === 1) {
        json.taskList[index].doneBy = $("#fUsername").val();
        json.taskList[index].doneAt = timeStr;
        formText = "Sind die angeforderten Unterlagen auf die Cloud gelegt worden, und möchten Sie die Aufgabe '" + json.taskList[index].taskTitle + "' als von Mandanten erledigt kennzeichnen?";
    }
    else if (json.taskList[index].status === 2) {
        json.taskList[index].archivedBy = $("#fUsername").val();
        json.taskList[index].archivedAt = timeStr;
        formText = "Soll die Aufgabe '" + json.taskList[index].taskTitle + "' als von Steuerberater erledigt markiert und archiviert werden?";
    }
    else if (json.taskList[index].status === 0) {
        json.taskList[index].archivedBy = "";
        json.taskList[index].archivedAt = "";
        json.taskList[index].doneBy ="";
        json.taskList[index].doneAt = "";
        formText = "Unbekannt";
    }

    OC.dialogs.prompt(
        formText,
        "Erledigt",
        (result, input) => {
            
            if (!result) {json=""; return;}
            if (input) {
                if (typeof json.taskList[index].history === 'undefined')
                    json.taskList[index].history = [];
                json.taskList[index].history.push(getModifierInformation(input));
            }
           
            // Populate
            serverData = json;
            updateModifierData()
            populateData();
            setctf();

            if (json.taskList[index].status === 1) {
                ctfSendNotificationNewReady(json.taskList[index].taskTitle);
              }
        },
        true,
        "Kommentar",
        false
    )

}

// **************************************
// Handler to undo task
// **************************************
function ctfBtnHandlerUndo(taskId) {

    let json = JSON.parse(JSON.stringify(serverData));

    // Find pos in tasklist
    let index = json.taskList.findIndex(function(item, i){
        return item.id === taskId;
    });

    // Modify
    if (json.taskList[index].status >= 0) {
        json.taskList[index].status = json.taskList[index].status - 1;
    }

    // Set Metadata
    const currentDate = new Date();
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeStr = currentDate.toLocaleDateString('de-DE', options);

    let formText = "";
    if (json.taskList[index].status === 1) {
        json.taskList[index].doneBy = $("#fUsername").val();
        json.taskList[index].doneAt = timeStr;
        formText = "Soll die Aufgabe '" + json.taskList[index].taskTitle + "' wieder vom Mandanten erledigt gekennzeichnet werden?"
    }
    else if (json.taskList[index].status === 2) {
        json.taskList[index].archivedBy = $("#fUsername").val();
        json.taskList[index].archivedAt = timeStr;
        json.taskList[index].doneBy ="";
        json.taskList[index].doneAt = "";
        formText = "Soll die Aufgabe '" + json.taskList[index].taskTitle + "' wieder als archiviert gekennzeichnet werden?"
    }
    else if (json.taskList[index].status === 0) {
        json.taskList[index].archivedBy = "";
        json.taskList[index].archivedAt = "";
        json.taskList[index].doneBy ="";
        json.taskList[index].doneAt = "";
        formText = "Soll die Aufgabe '" + json.taskList[index].taskTitle + "' wieder als Offen gekennzeichnet werden?"
    }

    OC.dialogs.prompt(
        formText,
        "Wiederherstellen",
        (result,input) => {
            
            if (!result) {json = ""; return;}
            if (input) {
                if (typeof json.taskList[index].history === 'undefined')
                    json.taskList[index].history = [];
                json.taskList[index].history.push(getModifierInformation(input));
            }
           
            // Populate
            serverData = json;
            updateModifierData()
            populateData();
            setctf();

            if (json.taskList[index].status === 0) {
              ctfSendNotificationNewTask(json.taskList[index].taskTitle);
            }
        },
        true,
        "Kommentar",
        false
    )

}

// **************************************
// Handler to remove task from list
// **************************************
function ctfBtnHandlerRemove(taskId) {
    
    let json = JSON.parse(JSON.stringify(serverData));

    // Find pos in tasklist
    let index = json.taskList.findIndex(function(item, i){
        return item.id === taskId;
    });

    OC.dialogs.confirmDestructive(
        "Möchten Sie wirklich die Aufgabe '" + json.taskList[index].taskTitle + "' löschen?",
        "Aufgabe löschen",
        {
            type: OC.dialogs.YES_NO_BUTTONS,
            confirm: "JA",
            confirmClasses: 'error',
            cancel: "Abbrechen",
        },
        (result) => {
            
            if (!result) {json=""; return;}

            // Remove Entry
            json.taskList.splice(index, 1);            

            // Populate
            serverData = json;
            updateModifierData()
            populateData();
            setctf();
        },
        true
    )
}

// **************************************
// Handler to edit description
// **************************************
function ctfBtnHandlerEdit(taskId) {
    
    // Prep Vars
    $('#modalDataDescPos').val(taskId);
    let editor = $("#fEditor").val();

    // Load serverData
    let json = JSON.parse(JSON.stringify(serverData));
     
    // Find pos in tasklist
    let index = json.taskList.findIndex(function(item, i){
        return item.id === taskId;
    });

    // Populate with existing data
    if (editor === "YES") {
        $("#formDescChange").val(json.taskList[index].taskDesciption);
    }
    else {
        $("#formDescChange").val(json.taskList[index].taskDesciption2);
    }

    // Display Modal
    $('#descModal').modal('show');   
}
function ctfBtnHandlerEditSave() {

    // Read and reset variables
    let taskId = $('#modalDataDescPos').val();   
    let editor = $("#fEditor").val();
     $('#modalDataDescPos').val("");

    // Load serverData
    let json = JSON.parse(JSON.stringify(serverData));
     
    // Find pos in tasklist
    let index = json.taskList.findIndex(function(item, i){
        return item.id === taskId;
    });

    // Change data
    if (editor === "YES") {
        json.taskList[index].taskDesciption = $("#formDescChange").val();
    }
    else {
        json.taskList[index].taskDesciption2 = $("#formDescChange").val();
    }

    // Reset Form
    $('#descModal').modal('hide');   
    $("#formDescChange").val("");
     
    // Populate
     serverData = json;
     updateModifierData()
     populateData();
     setctf();
     
}

// **************************************
// Handler to save email notificaiton
// **************************************
function ctfBtnHandlerEMailNotificaiton() {
    let editor = $("#fEditor").val();
    let email = $("#formEMailNotification").val();
    $('#notificationModal').modal('hide');   

    // Load serverData
    let json = JSON.parse(JSON.stringify(serverData));
    if (editor == "YES") {
        json.notificationKanzlei = email;
    }
    else {
        json.notificationMandant = email;
    }

    // Populate
    serverData = json;
    updateModifierData()
    populateData();
    setctf();
    
}

// ***************************************
// send notification
// ***************************************
function ctfSendNotificationNewTask(taskTitle) {    
    let editor = $("#fEditor").val();
    let json = JSON.parse(JSON.stringify(serverData));

    if (editor == "YES") {
        if (json.notificationMandant) {
            console.info("Send EMail notification - New Task to Mandant...");
            sendEmailNotification("NewTask",json.notificationMandant,json.title,taskTitle);  
        }        
    }
}
function ctfSendNotificationNewReady(taskTitle) {    
    let editor = $("#fEditor").val();
    let json = JSON.parse(JSON.stringify(serverData));

    if (editor == "NO") {
        if (json.notificationKanzlei) {
            console.info("Send EMail notification - New Ready to Kanzlei...");
            sendEmailNotification("NewReady",json.notificationKanzlei,json.title,taskTitle);   
        }           
    }
}
function sendEmailNotification(emailTemplate,receiver,fileTitle,taskTitle) {
    $.post(
        OC.generateUrl('/apps/ctfviewer/json/sendEmailNotification'),
        {
            emailTemplate: emailTemplate,
            receiver: receiver,
            fileTitle: fileTitle,
            taskTitle: taskTitle,
            user: $("#fUsername").val(),
        }
    ).done(function (data) {                        
        if (data.result == "OK") {           
            console.info("Successful added CTF email notification.")
        }
        else {
            // Save failed with error
            console.error("CTF email notificaiton failed: " + data);
        }       
    })
    .fail(function (data) {
        // Report Error
        console.error("CTF email notificaiton failed: " + data);
    });
}


