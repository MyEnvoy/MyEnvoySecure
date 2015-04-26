<?php

/**
 * @author Fabian Maier <fabian.maier@lacodon.de>
 */
final class Index {

    public static function init() {
        mb_internal_encoding('UTF-8');
        header("Content-Type: text/html; charset=utf-8");
    }

}

Index::init();
