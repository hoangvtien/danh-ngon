<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2016 VINADES., JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 3/9/2010 23:25
 */

if ( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

if ( ! nv_function_exists( 'nv_danh_ngon' ) )
{
    function nv_danh_ngon ( $block_config )
    {
        global $module_info, $lang_module, $site_mods, $db;
		
        $module = $block_config['module'];
        $data = $site_mods[$module]['module_data'];		
		
		$sql = "SELECT content FROM " . NV_PREFIXLANG . "_" . $data . " WHERE status=1 ORDER BY RAND() LIMIT 1";
		$result = $db->query( $sql );
		$numrow = $result->rowCount();
		
        if ( ! empty( $numrow ) )
        {
            if ( file_exists( NV_ROOTDIR . "/themes/" . $module_info['template'] . "/modules/danh-ngon/block_rand_tag.tpl" ) )
            {
                $block_theme = $module_info['template'];
            }
            else
            {
                $block_theme = "default";
            }
            $xtpl = new XTemplate( "block_rand_tag.tpl", NV_ROOTDIR . "/themes/" . $block_theme . "/modules/danh-ngon" );
			
			$ct = $result->fetchColumn();
			$xtpl->assign( 'CONTENT', $ct );
				
            $xtpl->parse( 'main' );
            return $xtpl->text( 'main' );
        }
    }
}

if ( defined( 'NV_SYSTEM' ) )
{
    global $site_mods;
	
    $module = $block_config['module'];
	
    if ( isset( $site_mods[$module] ) )
    {
        $content = nv_danh_ngon( $block_config );
    }
}