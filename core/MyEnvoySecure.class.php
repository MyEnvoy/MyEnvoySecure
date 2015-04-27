<?php

namespace MyEnvoySecure;

/**
 * Description of MyEnvoy
 *
 * @author Fabian
 */
class MyEnvoySecure {

    public static function getMysqli() {
        return new \mysqli(Config::getConfig('db')['host'], Config::getConfig('db')['username'], Config::getConfig('db')['password'], Config::getConfig('db')['dbname']);
    }

    public static function auth($channel, $hash) {
        $mysqli = self::getMysqli();

        $stmt = $mysqli->prepare("SELECT id FROM channel WHERE channel = ? AND hash = ? LIMIT 1");
        $stmt->bind_param('ss', $channel, $hash);
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->affected_rows;
        $stmt->close();
        $mysqli->close();

        return ($count === 1);
    }

    public static function newChannel($channel, $hash) {
        $mysqli = self::getMysqli();

        $insert = $mysqli->prepare("INSERT INTO channel (channel, hash) VALUES (?, ?)");
        $insert->bind_param('ss', $channel, $hash);
        $insert->execute();
        $count = $insert->affected_rows;
        $insert->close();
        $mysqli->close();

        return ($count === 1);
    }

    public static function save($channel, $hash, $data) {
        $mysqli = self::getMysqli();

        $insert = $mysqli->prepare("INSERT INTO msg (channel, data) VALUES ("
                . "(SELECT channel FROM channel WHERE channel = ? AND hash = ? LIMIT 1), ?)");
        $insert->bind_param('sss', $channel, $hash, $data);
        $insert->execute();
        $count = $insert->affected_rows;
        $insert->close();
        $mysqli->close();

        return ($count === 1);
    }

    /**
     * Use this function to get messages from a channel
     * @param string The channel identifier
     * @param string Hash of the password
     * @param int The last retrieved ID
     * @return array ('id', 'data')
     */
    public static function get($channel, $hash, $lastID) {
        $mysqli = self::getMysqli();

        $result = array();

        $stmt = $mysqli->prepare("SELECT msg.id, data FROM msg, channel WHERE channel.channel = ? AND channel.hash = ? "
                . "AND channel.channel = msg.channel AND msg.id > ?");
        $stmt->bind_param('ssi', $channel, $hash, $lastID);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $data);
        while ($stmt->fetch()) {
            $result[] = array('id' => $id, 'data' => $data);
        }
        $stmt->close();
        $mysqli->close();

        return $result;
    }

    /**
     * Use this function to render the messenger
     * @param string the html id/class prefix
     */
    public static function renderMessengerForm($element_prefix, $server_script) {
        printf('<div id="%1$s_window"><div><div id="%1$s_window_content_qr"></div><div id="%1$s_window_content"></div></div></div>
            <div id="%1$s_messenger_form">
            <div id="%1$s_alert"></div>
            <div id="%1$s_messenger_head">
                <input id="%1$s_name" class="%1$s_input" type="text" autofocus="autofocus" tabindex="1" placeholder="Name">
                <input id="%1$s_channel" class="%1$s_input" type="text" tabindex="4" placeholder="Channel">
                <input id="%1$s_password" class="%1$s_input" tabindex="2" type="password" placeholder="Password">
                <input id="%1$s_connect" class="%1$s_input" tabindex="3" type="submit" value="Connect">
                <button id="%1$s_share" title="Share Channel"></button>
            </div>
            <div id="%1$s_alternate">
                <hr>
                <span>or</span>
                <input id="%1$s_newpassword" class="%1$s_input" type="password" placeholder="Password">
                <input id="%1$s_new" class="%1$s_input" type="submit" value="Create new chat">
            </div>
            <div id="%1$s_loader">
                <img alt="Loading..." src="img/ajax-loader.gif">
            </div>
            <div id="%1$s_messenger_content">
                <div id="%1$s_message_log">

                </div>
                <div id="%1$s_scrollarrow"></div>
                <div id="%1$s_emoji_container">
                    <button id="%1$s_emoji_button"></button>
                </div>
                <textarea id="%1$s_message_txtArea" class="%1$s_input" placeholder="Message" maxlength="6000"></textarea>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                var mes = new MyEnvoySecure("%1$s", "%2$s");
            });
        </script>', $element_prefix, $server_script);
    }

}
