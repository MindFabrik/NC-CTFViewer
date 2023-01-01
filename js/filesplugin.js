// ----------------------------------------------------------
// Define Functions / Actions how will be used during 
// registering into NC. 
// ----------------------------------------------------------

// Function to register action and define default 
// Read sharingToken and file to open /ctfviewer/edit request
function editCTF(file, data) {

    // Open CTF clicked, building parameter
    var token = ($('#sharingToken').val() !== undefined) ? $('#sharingToken').val() : '';
    var dirLoad = data.dir.substr(1);
    if (dirLoad !== '') {
        dirLoad = dirLoad + '/';
    }

    // Building edit url
    if (token == '') {
        token = 'EMPTY';
    }
    var location = OC.generateUrl('apps/ctfviewer/edit?token={token}&file={file}', {
            'token': token,
            'file': dirLoad + file
        }, {escape: false}); 
    
    // Open Url
    window.location.href = location;
}

// Function to register new file entry in NC menu
// (All users can add a new CTF file)
let NewFileMenuPlugin = {

	attach: function(menu) {
		var fileList = menu.fileList;

		// only attach to main file list, public view is not supported yet
		if (fileList.id !== 'files') {
			return;
		}

		// register the new menu entry
		menu.addMenuEntry({
			id: 'file',
			displayName: "Neue Aufgabenliste",
			templateName: 'Aufgaben.ctf',
			iconClass: 'icon-projects',
			fileType: 'file',
			actionHandler: function(name) {
				var dir = fileList.getCurrentDirectory();
				// first create the file
				fileList.createFile(name).then(function() {
					// once the file got successfully created,
					// open the editor
					onCtfEditorTrigger(
						name,
						{
							fileList: fileList,
							dir: dir
						}
					);
				});
			}
		});
	}
};

// If a new CTF file is created, this function is called to perform 
// next steps. - Currently not in use
function onCtfEditorTrigger (filename, context) {
	// NOT IN USE
}



// ----------------------------------------------------------
// Registering 
// > Context Menu for CTF files
// > Default Action for CTF files
// > Add new CTF file menu
// ----------------------------------------------------------


// Register Action in Nextcloud (add entry in context menu of a CTF file)
OCA.Files.fileActions.registerAction({
    name: 'ctfviewerOpen',
    displayName: 'Aufgaben anzeigen',
    mime: 'application/ctf',
    permissions: OC.PERMISSION_READ,
    iconClass: 'icon-projects',
    actionHandler: editCTF,
});
// Define previous defined action as default action for CTF file
OCA.Files.fileActions.setDefault('application/ctf', 'ctfviewerOpen');
// Register new file entry for CTF files
OC.Plugins.register('OCA.Files.NewFileMenu', NewFileMenuPlugin);