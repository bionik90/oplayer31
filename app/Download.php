<?php
namespace App;
use Lib\Config;

use Lib\Helper;
use Manager\Playlist;
use Lib\Request;

class Download extends \Lib\Base\App {
	
	public function init() {
		$q = Request::get('query');
		if ( $q == 'm3u' && Config::getInstance()->getOption('widgets', 'downloadTrack') ) {
			$plId = Request::get('plId');
			$pl = new Playlist();
			
            if ( !( $playlist = $pl->getPlaylist($plId) ) ) {
				die;
			}
            
			$songs = $pl->getSongs($plId);
			
            $ret = "#EXTM3U\r\n";
            
			foreach($songs as $song) {
				$info = unserialize($song->songInfo);
                
				$q = http_build_query(array(
					'app' => 'ajax',
					'query' => 'dl',
					'artist' => html_entity_decode(trim($info['artist'])),
					'name' => html_entity_decode(trim($info['name'])),
					'id' => $info['id'],
					'url' => $info['url'],
				));
                
				$dlUrl = Config::getInstance()->getOption('app', 'baseUrl').'?'.$q;
				
                $ret .= "\r\n#EXTINF:".Helper::convertDuration($info['duration']).','.
					mb_convert_encoding(
						str_replace(array("\r","\n"), '', 
							$info['artist'].' - '.$info['name']),
						'Windows-1251',
						'UTF-8').
					"\r\n".$dlUrl."\r\n";
			}
            
			$fname = Helper::makeValidFname( $playlist->name . '.m3u' );
            
            header("Content-Disposition: attachment; filename=\"{$fname}\"");
            header('Content-Description: File Transfer');
            header('Content-Transfer-Encoding: binary');
            
            header('Last-Modified:');
//            header('ETag:');
            header('Content-Type: audio/x-mpegurl');
            header('Accept-Ranges: bytes');
            header('Content-Length: '.strlen($ret));
            
            echo $ret;
            die;
		}
	}
}