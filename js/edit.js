// *******************************************
// -------------  PAGE  READY ----------------
// Set event listener and request data from 
// server (getctf function)
// *******************************************
document.addEventListener('DOMContentLoaded', function () {
    $("#modalSaveBtn").click(function() {
        saveModalData();
    });
    $("#btnCloseForm").click(function() {
        closeViewer();
    });
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
    let obj = {
        "taskTitle": $("#formTitle").val(),
        "status": 0,
        "id": (+new Date).toString(36).slice(-10),
        "addedBy": $("#fUsername").val(),
        "addedAt": timeStr
    };
    serverData.taskList.push(obj);

    // Update Modifier Information
    updateModifierData();

    // Populate Data and clean up modal
    populateData();
    setctf();
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

// *******************************************
// Function to reset all modal data
// *******************************************
function cleanModalData() {
    $("#formTitle").val("");
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
    console.warn(json);
    
    // Build Header Information
    $("#ctfTitle").text(json.title);
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
        t.content.querySelector('.ctfBtnDone').dataset.taskid = element.id;
        t.content.querySelector('.ctfBtnRemove').dataset.taskid = element.id;
        
        if (element.status == 0) {
            countOpen = countOpen + 1;
            var clone = document.importNode(t.content, true); 
            document.querySelector('#tasksOpen').appendChild(clone);
        }
        else if (element.status == 1) {
            countReady = countReady + 1;
            var clone = document.importNode(t.content, true); 
            document.querySelector('#tasksReady').appendChild(clone);
        }
        else if (element.status == 2) {
            countDone = countDone + 1;
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
            
    })
})
    
    $("#ctfReady").show();
}

// **************************************
// Handler to mark task as done in list
// **************************************
function ctfBtnHandlerDone(taskId) {

    var json = serverData;

    // Find pos in tasklist
    var index = json.taskList.findIndex(function(item, i){
        return item.id === taskId;
    });

    // Modify
    if (json.taskList[index].status < 2) {
        json.taskList[index].status = json.taskList[index].status + 1;
    }

    // Populate
    serverData = json;
    updateModifierData()
    populateData();
    setctf();

}

// **************************************
// Handler to remove task from list
// **************************************
function ctfBtnHandlerRemove(taskId) {
    
    const json = serverData;

    // Find pos in tasklist
    var index = json.taskList.findIndex(function(item, i){
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
            
            if (!result) {return;}

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
