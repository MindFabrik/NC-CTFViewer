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

function onCtfEditorTrigger (filename, context) {
    console.warn("****");
    console.warn(filename);
    console.warn(context);
}

OCA.Files.fileActions.registerAction({
    name: 'ctfviewerOpen',
    displayName: 'Aufgaben anzeigen',
    mime: 'application/ctf',
    permissions: OC.PERMISSION_READ,
    iconClass: 'icon-projects',
    actionHandler: editCTF,
})
OCA.Files.fileActions.setDefault('application/ctf', 'ctfviewerOpen');
OC.Plugins.register('OCA.Files.NewFileMenu', NewFileMenuPlugin);