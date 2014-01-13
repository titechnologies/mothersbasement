function sendFunction(url){
    var request = new Ajax.Request(
        url,
        {
            method: 'get',
            onComplete: function(transport){
                try {
                    if ($('browser_window') && typeof(Windows) != 'undefined') {
                        Windows.focus('browser_window');
                        return;
                    }
                    var dialogWindow = Dialog.info(transport.responseText, {
                        closable:true,
                        resizable:false,
                        draggable:true,
                        className:'magento',
                        windowClassName:'popup-window',
                        title:'Your account identifiers',
                        top:100,
                        width:510,
                        height:165,
                        zIndex:1000,
                        recenterAuto:false,
                        hideEffect:Element.hide,
                        showEffect:Element.show,
                        id:'browser_window',
                        onClose:function (param, el) {
                            Windows.close('browser_window');
                            window.location.reload();
                        }
                    });
                    dialogWindow.content.addClassName('iwoca-content');
                    if(!FlashDetect.installed){
                        var accButton = document.getElementById("copy-acc2");
                        accButton.observe('click', function() {
                            document.getElementById("iwoca_general_account").select();
                        })
                        var passButton = document.getElementById("copy-pass2");
                        passButton.observe('click', function() {
                            document.getElementById("iwoca_general_password").select();
                        })
                    } else {
                        var accCopy = new ZeroClipboard(document.getElementById("copy-acc2"));
                        var passCopy = new ZeroClipboard( document.getElementById("copy-pass2"));
                    }
                } catch(e) {
                    alert('error');
                }
            }.bind(this)
        }
    );
}

document.observe('dom:loaded', function(){

    if (!FlashDetect.installed){
        var accButton = document.getElementById("copy-acc");
        accButton.observe('click', function() {
            document.getElementById("iwoca_general_account").select();
        })
        var passButton = document.getElementById("copy-pass");
        passButton.observe('click', function() {
            document.getElementById("iwoca_general_password").select();
        })
    } else {
        ZeroClipboard.setDefaults({moviePath: window.location.protocol + "//" + window.location.host + "/js/scandi_iwoca/ZeroClipboard.swf"});
        var accCopy = new ZeroClipboard(document.getElementById("copy-acc"));
        var passCopy = new ZeroClipboard( document.getElementById("copy-pass"));
    }
});

function deleteFunction(url){
    var request = new Ajax.Request(
        url,
        {
            method: 'get',
            onComplete: function(transport){
                try {
                    if ($('browser_window') && typeof(Windows) != 'undefined') {
                        Windows.focus('browser_window');
                        return;
                    }
                    var dialogWindow = Dialog.info(transport.responseText, {
                        closable:true,
                        resizable:false,
                        draggable:true,
                        className:'magento',
                        windowClassName:'popup-window',
                        title:'Extension successfully uninstalled',
                        top:100,
                        width:510,
                        height:165,
                        zIndex:1000,
                        recenterAuto:false,
                        hideEffect:Element.hide,
                        showEffect:Element.show,
                        id:'browser_window',
                        onClose:function (param, el) {
                            Windows.close('browser_window');
                            window.location.reload();
                        }
                    });
                    dialogWindow.content.addClassName('iwoca-content');
                } catch(e) {
                    alert('error');
                }
            }.bind(this)
        }
    );
}
