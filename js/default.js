function popupReference(refName,ambildata) {
    var w = 800;
    var h = 500;
    var l = (screen.width - w) / 2;
    var t = (screen.height - h) / 2;
    var param;

    if(typeof ambildata === 'undefined'){
        param = refName;
    }else{
        param = refName + ambildata;
    }

    oWindow = window.open('reference.php?s=' + param, 'winRef',
        'directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,width=' + w + ',height=' + h + ',left=' + l + ',top=' + t);
    oWindow.focus();
}

function popupReferenceAmbil(refName,ambildata) {
    var w = 800;
    var h = 500;
    var l = (screen.width - w) / 2;
    var t = (screen.height - h) / 2;
    var param;

    if(typeof ambildata === 'undefined'){
        param = refName;
    }else{
        param = refName + ambildata;
    }
    oWindow = window.open('reference.php?s=' + param, 'winRef',
        'directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,width=' + w + ',height=' + h + ',left=' + l + ',top=' + t);
    oWindow.focus();
}

function ymd2dmy(ymd) {
    var a = ymd.split("-");
    return a[2] + '-' + a[1] + '-' + a[0];
}

function popupForm(refName,ambildata) {
    var w = screen.width * 0.7;
    var h = screen.height * 0.7;
    var l = (screen.width - w) / 2;
    var t = (screen.height - h) / 2;
    var param;

    if(typeof ambildata === 'undefined'){
        param = refName;
    }else{
        param = refName + ambildata;
    }
    oWindow = window.open('module.php?m=' + param, 'winRef',
        'directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,width=' + w + ',height=' + h + ',left=' + l + ',top=' + t);
    oWindow.focus();
}