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

OCA.Files.fileActions.registerAction({
    name: 'ctfviewerOpen',
    displayName: 'Aufgaben anzeigen',
    mime: 'application/ctf',
    permissions: OC.PERMISSION_READ,
    iconClass: 'icon-projects',
    actionHandler: editCTF,
})
OCA.Files.fileActions.setDefault('application/ctf', 'ctfviewerOpen');