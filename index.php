<?php

    // Include the DirectoryLister class
    require_once('resources/DirectoryLister.php');
    require_once('resources/Torrent.php');

    // Initialize the DirectoryLister object
    $lister = new DirectoryLister();

    // Restrict access to current directory
    ini_set('open_basedir', getcwd());

    // Return file hash
    if (isset($_GET['hash'])) {

        // Get file hash array and JSON encode it
        $hashes = $lister->getFileHash($_GET['hash']);
        $data   = json_encode($hashes);

        // Return the data
        die($data);

    }
    if (isset($_GET['torrent'])) {

        $torrent = new Torrent( $_GET['torrent'] );
        //var_dump($torrent->info);die;
        echo '<dl>',
             '<dt>private: </dt><dd>', $torrent->is_private() ? 'yes' : 'no', '</dd>',
             '<dt>name: </dt><dd>', $torrent->name(), '</dd>',
             '<dt>publisher: </dt><dd>', $torrent->publisher(), '</dd>',
             '<dt>date: </dt><dd>', date('Y-m-d H:i:s', $torrent->creation_date()), '</dd>',
             '<dt>announce: </dt><dd>', torrent_announce($torrent->announce()), '</dd>',
             '<dt>piece_length: </dt><dd>', Torrent::format($torrent->piece_length()), '</dd>',
             '<dt>size: </dt><dd>', $torrent->size( 2 ), '</dd>',
             '<dt>hash info: </dt><dd>', $torrent->hash_info(), '</dd>',
             '<dt>comment: </dt><dd>', $torrent->comment(), '</dd>',
             '</dl>';
             //'<br>announce: '; var_dump( $torrent->announce() );
             //'<br>stats: '; var_dump( $torrent->scrape() );
             //echo '<br>source: ', $torrent;
        $files = torrent_files($torrent->name(), $torrent->content());
        echo '<ul>';
        foreach ($files as $key => $value) {
            echo '<li>', $value['name'], ' ', $value['size'], '</li>';
        }
        echo '</ul>';
        die;
    }

    if (isset($_GET['zip'])) {

        $dirArray = $lister->zipDirectory($_GET['zip']);

    } else {

        // Initialize the directory array
        if (isset($_GET['dir'])) {
            $dirArray = $lister->listDirectory($_GET['dir']);
        } else {
            $dirArray = $lister->listDirectory('.');
        }

        // Define theme path
        if (!defined('THEMEPATH')) {
            define('THEMEPATH', $lister->getThemePath());
        }

        // Set path to theme index
        $themeIndex = $lister->getThemePath(true) . '/index.php';

        // Initialize the theme
        if (file_exists($themeIndex)) {
            include($themeIndex);
        } else {
            die('ERROR: Failed to initialize theme');
        }

    }
    function torrent_announce($content){
        if(is_array($content)){
            if(is_array($content[0])){
                return $content[0][0];
            }else{
                return $content[0];
            }
        }else{
            return $content;
        }
    }
    function torrent_files($name, $content){
        $arr = [];
        foreach ($content as $key => $value) {
            if(!stristr($key, 'BitComet')){
                $key = str_replace($name, "", $key);
                $arr[] = ['name' => $key, 'size' => Torrent::format($value)];
            }
        }
        return $arr;
    }
