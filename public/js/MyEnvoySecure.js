/* global CryptoJS, emoji_icons */

var MyEnvoySecure = function (element_prefix, server_script) {

    var _channel;
    var _hash;
    var _pwd;
    var _id = 0;
    var _prefix = element_prefix;
    var _receiveIntervall = 2000;
    var _server = server_script;
    var _playSound = false;
    var _newMessageCount = 0;

    var _favicon = new Favico({
        animation: 'none'
    });
    var _audioElement;
    var _wysiwyg;

    $(window).blur(function () {
        _playSound = true;
    }).focus(function () {
        _playSound = false;
        _newMessageCount = 0;
        _favicon.badge(_newMessageCount);
    });

    var showMessenger = function () {
        _wysiwyg = $('textarea#' + _prefix + '_message_txtArea').emojiarea({
            button: '#' + _prefix + '_emoji_button'
        });
        $('div.emoji-wysiwyg-editor').on("keydown", messageBoxSend);
        $('#' + _prefix + '_messenger_content').fadeIn();
        setInterval(receive, _receiveIntervall);
        receive();
    };

    var showLoader = function () {
        $('#' + _prefix + '_loader').show();
    };

    var hideLoader = function () {
        $('#' + _prefix + '_loader').hide();
    };

    var hideAlternate = function () {
        $('#' + _prefix + '_alternate').hide();
    };

    var receive = function () {
        var needScrolling = isScrolledToBottom();
        var oldId = _id;
        $.ajax({
            url: _server + '?state=get',
            method: 'POST',
            dataType: 'jsonp',
            data: {hash: _hash, channel: _channel, id: _id}
        }).done(function (data) {
            var myName = $('#' + _prefix + '_name').val();
            var dataLenght = data.length;
            for (var i = 0; i < dataLenght; i++) {
                var messageData = decryptData(data[i].data).toString(CryptoJS.enc.Utf8);
                messageData = $.parseJSON(messageData);
                var name = messageData.name;
                var content = messageData.content;
                // replace emojis
                content = content.supplant($.emojiarea.iconsReplace[0]['icons']);
                var date = messageData.date;
                _id = data[i].id;
                var msgClass = _prefix + '_you';
                if (name === myName) {
                    msgClass = _prefix + '_me';
                }
                var msg = '<div class="' + _prefix + '_message ' + msgClass + '">\
                        <div class = "' + _prefix + '_message_head">\
                            ' + name + '\
                        </div>\
                        <div class = "' + _prefix + '_message_content">\
                            ' + content + '\
                        </div>\
                        <div class = "' + _prefix + '_message_footer">\
                            ' + getTimeString(date) + '\
                        </div>\
                    </div>\
                    <div class = "' + _prefix + '_clear"></div>';

                $('#' + _prefix + '_message_log').append(msg);
                if (_playSound) {
                    playSound();
                    _newMessageCount++;
                    _favicon.badge(_newMessageCount);
                }
            }
            if (needScrolling) {
                scrollToEnd();
            } else if (oldId < _id) {
                showScrollArrow();
            }
            hideAlert();
        }).fail(function () {
            requestFail();
        });
    };

    var messageBoxSend = function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            var msg = _wysiwyg.val();
            if ($('#' + _prefix + '_name').val() === '') {
                showAlert("Please enter a name.", true);
                return;
            }

            var data = {
                name: ($('#' + _prefix + '_name').val()),
                content: msg,
                date: getTimeStamp()
            };

            data = encryptData(JSON.stringify(data)).toString();

            if (msg === '') {
                return;
            }

            $.ajax({
                type: "POST",
                url: _server + '?state=send',
                dataType: 'jsonp',
                data: {channel: _channel, hash: _hash, data: data}
            }).done(function (data) {
                if (data.status === false) {
                    showAlert("The message couldn't be sent! Please try to reload the page.", true);
                    $('div.emoji-wysiwyg-editor').html(msg);
                } else {
                    hideAlert();
                }
            }).fail(function () {
                requestFail();
                $('div.emoji-wysiwyg-editor').html(msg);
            });
            $('div.emoji-wysiwyg-editor').empty();
        }
    };

    $('#' + _prefix + '_connect').click(function () {
        showLoader();
        var hash = $('#' + _prefix + '_password').val();
        var channel = $('#' + _prefix + '_channel').val();
        var name = $('#' + _prefix + '_name').val();
        if (hash === '' || channel === '' || name === '') {
            showAlert("Please fill all fields.", true);
            hideLoader();
            return;
        }

        var hash = hashPassword(hash);
        $.ajax({
            url: _server + '?state=auth',
            method: 'POST',
            dataType: 'jsonp',
            data: {hash: hash, channel: channel}
        }).done(function (data) {
            if (data.status === true) {
                $('#' + _prefix + '_connect').hide();
                $('#' + _prefix + '_password').hide();
                _channel = channel;
                _hash = hash;
                _pwd = $('#' + _prefix + '_password').val();
                $('#' + _prefix + '_share').show();
                hideAlternate();
                showMessenger();
            } else {
                showAlert("The password or channel ID is wrong, please try again.", true);
            }
        }).fail(function () {
            requestFail();
        });
        hideLoader();
    });

    $('#' + _prefix + '_share').click(function () {
        if (_channel !== null) {
            $('#' + _prefix + '_window').show();
            var url = window.location.protocol + '//'
                    + window.location.host + window.location.pathname
                    + '#' + _channel;
            $('#' + _prefix + '_window_content').html('<a href="' + url + '" target="_blank">Link</a>');
            $('#' + _prefix + '_window_content_qr').html('');
            $('#' + _prefix + '_window_content_qr').qrcode({
                "render": 'canvas',
                "minVersion": 1,
                "maxVersion": 40,
                "ecLevel": 'L',
                "size": 400,
                "left": 0,
                "top": 0,
                "text": url,
                "quiet": 0,
                "mode": 0,
                "mSize": 0.1,
                "mPosX": 0.5,
                "mPosY": 0.5
            });
        }
    });

    $('#' + _prefix + '_new').click(function () {
        showLoader();
        var hash = $('#' + _prefix + '_newpassword').val();
        if (hash === '') {
            showAlert("Please enter a strong, unique password for the new chat.", true);
            hideLoader();
            return;
        }

        var hash = hashPassword(hash);
        $.ajax({
            url: _server + '?state=new',
            method: 'POST',
            dataType: 'jsonp',
            data: {hash: hash}
        }).done(function (data) {
            if (data.status === true) {
                $('#' + _prefix + '_channel').val(data.channel);
                $('#' + _prefix + '_password').val($('#' + _prefix + '_newpassword').val());
                hideAlternate();
            } else {
                showAlert("Ops! There have been created to many chats today, please try again tomorrow or contact the admin.", true);
            }
        }).fail(function () {
            requestFail();
        });
        hideLoader();
    });

    var playSound = function () {
        // create Audioelement
        _audioElement = new Audio('');

        // insert into DOM to make it playable
        document.body.appendChild(_audioElement);

        // get mediatype
        var canPlayType = _audioElement.canPlayType('audio/wav');
        if (canPlayType.match(/maybe|probably/i)) {
            _audioElement.src = 'js/notification.wav';
        }

        // play if mp3 is downloaded
        _audioElement.addEventListener('canplay', function () {
            _audioElement.play();
        }, false);
    };

    var requestFail = function () {
        showAlert('You are offline, please check your internet connection.', false);
    };

    $('#' + _prefix + '_window').click(function () {
        $(this).hide();
    });

    $('#' + _prefix + '_alert').click(function () {
        hideAlert();
    });

    $('#' + _prefix + '_scrollarrow').click(function () {
        scrollToEnd();
    });

    var showAlert = function (message, timeout) {
        $('#' + _prefix + '_alert').html(message);
        $('#' + _prefix + '_alert').show();
        if (timeout) {
            setTimeout(hideAlert, 5000);
        }
    };

    var hideAlert = function () {
        $('#' + _prefix + '_alert').hide();
    };

    var scrollToEnd = function () {
        var d = $('#' + _prefix + '_message_log');
        d.scrollTop(d.prop('scrollHeight'));
        hideScrollArrow();
    };

    var isScrolledToBottom = function () {
        var d = $('#' + _prefix + '_message_log');
        if (d.scrollTop() === (d.prop('scrollHeight') - d.height())) {
            return true;
        }
        return false;
    };

    var showScrollArrow = function () {
        $('#' + _prefix + '_scrollarrow').show();
    };

    var hideScrollArrow = function () {
        $('#' + _prefix + '_scrollarrow').hide();
    };

    var hashPassword = function (password) {
        return CryptoJS.SHA3(password, {outputLength: 512}).toString();
    };

    var encryptData = function (data) {
        return CryptoJS.AES.encrypt(data, _pwd);
    };

    var decryptData = function (data) {
        return CryptoJS.AES.decrypt(data, _pwd);
    };

    var getTimeString = function (stamp) {
        var date = new Date(stamp);

        var month = "0" + (date.getMonth() + 1);
        var day = "0" + date.getDate();
        var year = date.getFullYear();
        var hours = "0" + date.getHours();
        var minutes = "0" + date.getMinutes();

        return day.substr(day.length - 2) + '.' + month.substr(month.lengh - 2) + '.' + year + ' ' + hours.substr(hours.length - 2) + ':' + minutes.substr(minutes.length - 2);
    };

    var getTimeStamp = function () {
        return Date.now();
    };

    $(window).bind('beforeunload', function () {
        return 'Don\'t forget to remember the channel!';
    });

    var _locationHash = window.location.hash.substring(1);
    if (_locationHash !== '') {
        $('#' + _prefix + '_channel').val(_locationHash);
        hideAlternate();
    }

    if (typeof String.prototype.supplant !== 'function') {
        String.prototype.supplant = function (o) {
            return this.replace(/:([^{\s::}]*):/g, function (a, b) {
                var r = o[b];
                return typeof r === 'string' ? '<img src="' + $.emojiarea.pathReplace + r + '" alt="emoji">' : a;
            });
        };
    }
};
