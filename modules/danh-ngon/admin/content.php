<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2016 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 22/2/2011, 22:49
 */

if ( ! defined( 'NV_IS_DANH_NGON_ADMIN' ) ) die( 'Stop!!!' );

$page_title = $lang_module['content_title'];

$id = $nv_Request->get_int( 'id', 'get', 0 );
$error = "";

if( $id )
{
	$sql = "SELECT * FROM " . NV_PREFIXLANG . "_" . $module_data . " WHERE id=" . $id;
	$result = $db->query( $sql );
	$check = $result->rowCount();
		
	if ( $check != 1 )
	{
		nv_info_die( $lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content'] );
	}
		
	$row = $result->fetch();
		
	$array_old = $array = array(
		"content" => $row['content'],
		"tags" => empty( $row['tags'] ) ? array() : explode( "|", $row['tags'] ),
		"tags_news" => "",
	);
		
	$form_action = NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=" . $op . "&amp;id=" . $id;
	$table_caption = $lang_module['content_edit'];
}
else
{
	$form_action = NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=" . $op;
	$table_caption = $lang_module['content'];
	
	$array = array(
		"content" => "",
		"tags" => array(),
		"tags_news" => "",
	);
}

if ( $nv_Request->isset_request( 'submit', 'post' ) )
{
	$array['content'] = $nv_Request->get_title( 'content', 'post', '', 1 );
	
	$array['tags'] = array();
	$array['tags'] = $nv_Request->get_typed_array( 'tags', 'post', 'string' );
	
	$array['tags_news'] = $nv_Request->get_title( 'tags_news', 'post', '', 1 );
	
	// Xu ly tu khoa moi
	if( ! empty( $array['tags_news'] ) )
	{
		$tags_news = $array['tags_news'];
		$array['tags_news'] = array();
		$tags_news = array_unique(array_filter(array_map("nv_strtolower",array_map("trim",explode(",", $tags_news)))));
		$array['tags_news'] = $tags_news;
		
		if( ! empty( $array['tags_news'] ) )
		{
			foreach( $array['tags_news'] as $tags )
			{
				// Them vao tags
				if( ! in_array( $tags, $array['tags'] ) )
				{
					$array['tags'][] = $tags;
				}
			
				// Them vao bang tags
				$sql = "SELECT title FROM " . NV_PREFIXLANG . "_" . $module_data . "_tags WHERE title=" . $db->quote( $tags );
				$result = $db->query( $sql );
				if( ! $result->rowCount() )
				{
					$sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $module_data . "_tags VALUES ( " . $db->quote( $tags ) . ", 0, 1 )";
					$db->query( $sql );
				}
			}
		}
	}
	
	if ( empty ( $array['content'] ) )
	{
		$error = $lang_module['content_error_empty'];
	}
	elseif( empty( $array['tags'] ) )
	{
		$error = $lang_module['content_error_tags'];
	}
	else
	{
		if( empty( $id ) )
		{
			$sql = "SELECT id FROM " . NV_PREFIXLANG . "_" . $module_data . " WHERE content=" . $db->quote( $array['content'] );
			$result = $db->query( $sql );
			list ( $check_exist ) = $result->fetch(3);
			
			if ( $check_exist )
			{
				$error = $lang_module['content_error_exist'];
			}
			else
			{
				$sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $module_data . " VALUES (
					NULL, 
					" . $db->quote( implode( '|', $array['tags'] ) ) . ", 
					" . $db->quote( $array['content'] ) . ",
					" . NV_CURRENTTIME . ",
					" . NV_CURRENTTIME . ",
					1
				)";
					
				$id_result = $db->insert_id( $sql );
				
				if ( $id_result )
				{
					//$xxx->closeCursor();
					$nv_Cache->delMod( $module_name );
					
					foreach( $array['tags'] as $tags )
					{
						$sql = "UPDATE " . NV_PREFIXLANG . "_" . $module_data . "_tags SET nums=nums+1 WHERE title=" . $db->quote( $tags );
						$db->query( $sql );
					}
					
					nv_insert_logs( NV_LANG_DATA, $module_name, $lang_module['content'], "", $admin_info['userid'] );
					Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=main" );
					die();
				}
				else
				{
					$error = $lang_module['error_save'];
				}
			}
		}
		else
		{
			$sql = "SELECT id FROM " . NV_PREFIXLANG . "_" . $module_data . " WHERE content=" . $db->quote( $array['content'] ) . " AND id!=" . $id;
			$result = $db->query( $sql );
			list ( $check_exist ) = $db->sql_fetchrow( $result );
			
			if ( $check_exist )
			{
				$error = $lang_module['content_error_exist'];
			}
			else
			{
				$sql = "UPDATE " . NV_PREFIXLANG . "_" . $module_data . " SET 
					tags=" . $db->quote( implode( '|', $array['tags'] ) ) . ", 
					content=" . $db->quote( $array['content'] ) . "
				WHERE id =" . $id;
					
				if ( $db->query( $sql ) )
				{
					//$xxx->closeCursor();
					$nv_Cache->delMod( $module_name );
					
					foreach( $array['tags'] as $tags )
					{
						if( ! in_array( $tags, $array_old['tags'] ) )
						{
							$sql = "UPDATE " . NV_PREFIXLANG . "_" . $module_data . "_tags SET nums=nums+1 WHERE title=" . $db->quote( $tags );
							$db->query( $sql );
						}
					}
					foreach( $array_old['tags'] as $tags )
					{
						if( ! in_array( $tags, $array['tags'] ) )
						{
							$sql = "UPDATE " . NV_PREFIXLANG . "_" . $module_data . "_tags SET nums=nums-1 WHERE title=" . $db->quote( $tags );
							$db->query( $sql );
						}
					}
					
					nv_insert_logs( NV_LANG_DATA, $module_name, $lang_module['content_edit'], $id, $admin_info['userid'] );
					Header( "Location: " . NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . "=main" );
					exit();
				}
				else
				{
					$error = $lang_module['error_update'];
				}
			}
		}
	}
}

$xtpl = new XTemplate( "content.tpl", NV_ROOTDIR . "/themes/" . $global_config['module_theme'] . "/modules/" . $module_file );
$xtpl->assign( 'LANG', $lang_module );
$xtpl->assign( 'GLANG', $lang_global );
$xtpl->assign( 'DATA', $array );
$xtpl->assign( 'TABLE_CAPTION', $table_caption );
$xtpl->assign( 'FORM_ACTION', $form_action );
$xtpl->assign( 'NV_BASE_ADMINURL', NV_BASE_ADMINURL );
$xtpl->assign( 'NV_NAME_VARIABLE', NV_NAME_VARIABLE );

// Tat cac cac tags
$sql = "SELECT title FROM " . NV_PREFIXLANG . "_" . $module_data . "_tags ORDER BY title ASC";
$result = $db->query( $sql );

$i = 1;
while( list( $tags ) = $result->fetch( 3 ) )
{
	$xtpl->assign( 'TAGS', $tags );
	$xtpl->assign( 'CHECKED', in_array( $tags, $array['tags'] ) ? " checked=\"checked\"" : "" );
	
	if( ( $i % 4 ) == 0 )
	{
		$xtpl->parse( 'main.tags.break' );
	}
	
	$xtpl->parse( 'main.tags' );
	$i ++;
}

// Prase error
if ( ! empty ( $error ) )
{
	$xtpl->assign( 'ERROR', $error );
	$xtpl->parse( 'main.error' );
}
	
$xtpl->parse( 'main' );
$contents = $xtpl->text( 'main' );

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme( $contents );
include NV_ROOTDIR . '/includes/footer.php';