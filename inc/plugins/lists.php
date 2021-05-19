<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("admin_load", "lists_manage_lists");
$plugins->add_hook("admin_config_action_handler", "lists_admin_config_action_handler");
$plugins->add_hook("admin_config_permissions", "lists_admin_config_permissions");
$plugins->add_hook("admin_config_menu", "lists_admin_config_menu");
$plugins->add_hook("admin_formcontainer_output_row", "lists_permission"); 
$plugins->add_hook("admin_user_groups_edit_commit", "lists_permission_commit"); 

function lists_info()
{
    global $lang;
    $lang->load('lists');

    // Plugin Info
    $lists = [
        "name" => $lang->lists_name,
        "description" => $lang->lists_short_desc,
        "website" => "https://github.com/itssparksfly",
        "author" => "sparks fly",
        "authorsite" => "https://github.com/itssparksfly",
        "version" => "1.0",
        "compatibility" => "18*"
    ];

    return $lists;
}

function lists_install() {
    global $db, $cache;

     // Catch potential errors [duplicates]
     $tables = [
        "lists"
    ];

    foreach($tables as $table) {
        if ($db->table_exists($table)) {
            $db->drop_table($table);
        }
    }  
    
    $collation = $db->build_create_table_collation();

    // create table "lists"
    $db->write_query("
        CREATE TABLE ".TABLE_PREFIX."lists (
            `lid` int(10) unsigned NOT NULL auto_increment,
            `name` varchar(255) NOT NULL DEFAULT '',
            `key` varchar(255) NOT NULL DEFAULT '',
            `text` text NOT NULL default '',
            `fid` varchar(5) NOT NULL DEFAULT '', 
            `filter` varchar(255) NOT NULL DEFAULT '', 
            `extras` varchar(255) NOT NULL DEFAULT '', 
            `sortby` varchar(255) NOT NULL DEFAULT 'username', 
            PRIMARY KEY (lid)
        ) ENGINE=MyISAM{$collation};
    ");

     // add table field => group permissions
     if(!$db->field_exists("showinlists", "usergroups"))
     {
         switch($db->type)
         {
             case "pgsql":
                 $db->add_column("usergroups", "showinlists", "smallint NOT NULL default '1'");
                 break;
             default:
                 $db->add_column("usergroups", "showinlists", "tinyint(1) NOT NULL default '1'");
                 break;
 
         }
     } 
     $cache->update_usergroups();

}

function lists_activate() {
    global $db;

    // Add templategroup
    $templategrouparray = [
        'prefix' => 'lists',
        'title'  => $db->escape_string($lang->lists_templates),
        'isdefault' => 1
    ];
    $db->insert_query("templategroups", $templategrouparray);

    $lists = [
        'title' => 'lists',
        'template' => $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->lists}</title>
		{$headerinclude}</head>
		<body>
		{$header}
			<table width="100%" cellspacing="5" cellpadding="5">
				<tr>
					{$menu}
					<td valign="top" class="trow1">
						<div style="text-align: justify; width: 70%; margin: 20px auto;">
							{$lang->lists_desc} 
						</div>
					</td>
				</tr>
			</table>
		{$footer}
		</body>
		</html>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $lists);

    $lists_menu = [
        'title' => 'lists_menu',
        'template' => $db->escape_string('<td width="20%" valign="top">
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
        <tbody>
            <tr>
                <td class="thead"><strong><a href="lists.php">{$lang->lists}</a></strong></td>
            </tr>
            {$menu_bit}
        </tbody>
        </table>
    </td>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $lists_menu);

    $lists_menu_bit = [
        'title' => 'lists_menu_bit',
        'template' => $db->escape_string('<tr>
		<td class="trow1 smalltext"><a href="lists.php?action={$list[\'key\']}">{$list[\'name\']}</a></td>
</tr>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $lists_menu_bit);

    $lists_list = [
        'title' => 'lists_list',
        'template' => $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$list[\'name\']}</title>
		{$headerinclude}</head>
		<body>
		{$header}
			<table width="100%" cellspacing="5" cellpadding="5">
				<tr>
					{$menu}
					<td valign="top" class="trow1">
						<div style="text-align: justify; width: 85%; margin: 20px auto;">
                        <h1>{$list[\'name\']}</h1>
							{$list[\'text\']} <br /><br />
                            {$list_bit}
						</div>
					</td>
				</tr>
			</table>
		{$footer}
		</body>
		</html>'),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $lists_list);

    $lists_list_bit = [
        'title' => 'lists_list_bit',
        'template' => $db->escape_string('<h2>{$option}</h2>
        {$list_bit_user}'),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $lists_list_bit);

    $lists_list_bit_user = [
        'title' => 'lists_list_bit_user',
        'template' => $db->escape_string('{$profilelink} {$extrainfo}<br />'),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $lists_list_bit_user);

}

function lists_is_installed() {
    global $db;
    if ($db->table_exists("lists")) {
        return true;
    } else {
        return false;
    }
}

function lists_uninstall() {
    global $db;

    $tables = [
        "lists"
    ];
    
    foreach($tables as $table) {
        if($db->table_exists($table)) {
            $db->drop_table($table);
        }
    }

    // drop fields
	if($db->field_exists("showinlists", "usergroups"))
	{
    	$db->drop_column("usergroups", "showinlists");
	}
}

function lists_deactivate() {
    global $db;
    $db->delete_query("templategroups", "prefix = 'lists'");
    $db->delete_query("templates", "title LIKE 'lists%'");
}

function lists_admin_config_action_handler(&$actions)
{
    $actions['lists'] = array('active' => 'lists', 'file' => 'lists');
}

function lists_admin_config_permissions(&$admin_permissions)
{
    global $lang;
    $lang->load('lists');

    $admin_permissions['lists'] = $lang->lists_permission;
}

function lists_admin_config_menu(&$sub_menu)
{
    global $mybb, $lang;
    $lang->load('lists');
    
    $sub_menu[] = [
        "id" => "lists",
        "title" => $lang->lists_name,
        "link" => "index.php?module=config-lists"
    ];
}

function lists_manage_lists() {
    global $mybb, $db, $lang, $page, $run_module, $action_file;
    $lang->load('lists');

    if ($page->active_action != 'lists') {
        return false;
    }

    if ($run_module == 'config' && $action_file == 'lists') {
        
        // lists overview
        if ($mybb->input['action'] == "" || !isset($mybb->input['action'])) {

            // Add to page navigation
            $page->add_breadcrumb_item($lang->lists_name);

            // Build options header
            $page->output_header($lang->lists_name." - ".$lang->lists_create);
            $sub_tabs['lists'] = [
                "title" => $lang->lists_create,
                "link" => "index.php?module=config-lists",
                "description" => $lang->lists_create_desc                
            ];
            $sub_tabs['lists_list_add'] = [
                "title" => $lang->lists_create_submit,
                "link" => "index.php?module=config-lists&amp;action=add_list",
                "description" => $lang->lists_create_submit_desc
            ];

            $page->output_nav_tabs($sub_tabs, 'lists');

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Build the overview
            $form = new Form("index.php?module=config-lists", "post");

            $form_container = new FormContainer($lang->lists_create);
            $form_container->output_row_header($lang->lists_name);
            $form_container->output_row_header("<div style=\"text-align: center;\">".$lang->lists_create_options."</div>");

            // Get all entries
            $query = $db->simple_select("lists", "*", "",
                ["order_by" => 'name', 'order_dir' => 'ASC']);
 
            while($all_lists = $db->fetch_array($query)) {

                $form_container->output_cell('<strong>'.htmlspecialchars_uni($all_lists['name']).'</strong>');
                $popup = new PopupMenu("lists_{$all_lists['lid']}", $lang->lists_create_edit);
                $popup->add_item(
                    $lang->lists_create_edit,
                    "index.php?module=config-lists&amp;action=edit_list&amp;lid={$all_lists['lid']}"
                );
                $popup->add_item(
                    $lang->lists_create_delete,
                    "index.php?module=config-lists&amp;action=delete_list&amp;lid={$all_lists['lid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), array("class" => "align_center"));
                $form_container->construct_row();
            }

            $form_container->end();
            $form->end();
            $page->output_footer();

            exit;
        }

        if ($mybb->input['action'] == "add_list") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['name'])) {
                    $errors[] = $lang->lists_create_error_no_title;
                }
                if (empty($mybb->input['key'])) {
                    $errors[] = $lang->lists_create_error_no_key;
                }
                if (empty($mybb->input['fid'])) {
                    $errors[] = $lang->lists_create_error_no_fid;
                }

                if(!empty($mybb->input['extras'])) {
                    $extralist = implode(",", $mybb->input['extras']);
                }

                // No errors - insert
                if (empty($errors)) {
                    $new_list = [
                        "name" => $db->escape_string($mybb->input['name']),
                        "text" => $db->escape_string($mybb->input['text']),
                        "key" => $db->escape_string($mybb->input['key']),
                        "fid" => $db->escape_string($mybb->input['fid']),
                        "filter" => $db->escape_string($mybb->input['filter']),
                        "extras" => $extralist,
                        "sortby" => $db->escape_string($mybb->input['sorted']),
                    ];

                    $db->insert_query("lists", $new_list);

                    $mybb->input['module'] = "lists";
                    $mybb->input['action'] = $lang->lists_created;
                    log_admin_action(htmlspecialchars_uni($mybb->input['name']));

                    flash_message($lang->lists_created, 'success');
                    admin_redirect("index.php?module=config-lists");
                }
            }

                $page->add_breadcrumb_item($lang->lists_create_submit);
                // Editor scripts
                $page->extra_header .= <<<EOF
	<link rel="stylesheet" href="../jscripts/sceditor/themes/mybb.css" type="text/css" media="all" />
	<script type="text/javascript" src="../jscripts/sceditor/jquery.sceditor.bbcode.min.js?ver=1822"></script>
	<script type="text/javascript" src="../jscripts/bbcodes_sceditor.js?ver=1827"></script>
	<script type="text/javascript" src="../jscripts/sceditor/plugins/undo.js?ver=1805"></script>
EOF;

            // Build options header
            $page->output_header($lang->lists_name." - ".$lang->lists_create);
            $sub_tabs['lists'] = [
                "title" => $lang->lists_create,
                "link" => "index.php?module=config-lists",
                "description" => $lang->lists_create_desc                
            ];
            $sub_tabs['lists_list_add'] = [
                "title" => $lang->lists_create_submit,
                "link" => "index.php?module=config-lists&amp;action=add_list",
                "description" => $lang->lists_create_submit_desc
            ];

            $page->output_nav_tabs($sub_tabs, 'lists_list_add');

                // Show errors
                if (isset($errors)) {
                    $page->output_inline_error($errors);
                }

                // Build the form
                $form = new Form("index.php?module=config-lists&amp;action=add_list", "post", "", 1);
                $form_container = new FormContainer($lang->lists_create_submit);

                $form_container->output_row(
                    $lang->lists_create_name."<em>*</em>",
                    $lang->lists_create_name_desc,
                    $form->generate_text_box('name', $mybb->input['name'])
                );

                $form_container->output_row(
                    $lang->lists_create_key."<em>*</em>",
                    $lang->lists_create_key_desc,
                    $form->generate_text_box('key', $mybb->input['key'])
                );


                $text_editor = $form->generate_text_area('text', $mybb->input['text'], array(
                    'id' => 'text',
                    'rows' => '25',
                    'cols' => '70',
                    'style' => 'height: 250px; width: 75%'
                    )
                );
                
                $text_editor .= build_mycode_inserter('text');
                $form_container->output_row(
                    $lang->lists_create_text,
                    $lang->lists_create_text_desc,
                    $text_editor,
                    'text'
                );

                $fields = [];
                $sort = [];
                $sort["username"] = $lang->lists_username;
                $query = $db->simple_select("profilefields", "fid,name");
                while($result = $db->fetch_array($query)) {
                    $fid = $result['fid'];
                    $fields[$fid] = $result['name'];
                    $sort[$fid] = $result['name'];
                }

                $fields[-1] = $lang->lists_usergroup;
                $fields[-2] = $lang->lists_additionalgroup;
                $fields[-3] = $lang->lists_displaygroup;
                $fields[-4] = $lang->lists_username;


                $form_container->output_row(
                    $lang->lists_create_fid, 
                    $lang->lists_create_fid_desc, 
                    $form->generate_select_box('fid', 
                        $fields, 
                        '', 
                        array('id' => 'fid')),
                    'fid');

                    $form_container->output_row(
                        $lang->lists_create_filter,
                        $lang->lists_create_filter_desc,
                        $form->generate_text_box('filter', $mybb->input['filter'])
                    );
            
                    $form_container->output_row(
                        $lang->lists_create_sortby, 
                        $lang->lists_create_sortby_desc, 
                        $form->generate_select_box('sorted', 
                            $sort, 
                            '', 
                            array('id' => 'sort')),
                        'sorted');
    

                    $form_container->output_row(
                        $lang->lists_create_extras, 
                        $lang->lists_create_extras_desc, 
                        $form->generate_select_box('extras[]', 
                            $fields, 
                            '', 
                            array('id' => 'fid', 'multiple' => true, 'size' => 5)),
                        'extras');

                $form_container->end();
                $buttons[] = $form->generate_submit_button($lang->lists_create_submit);
                $form->output_submit_wrapper($buttons);
                $form->end();
                $page->output_footer();
    
                exit;         
        }
        if ($mybb->input['action'] == "edit_list") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['name'])) {
                    $errors[] = $lang->lists_create_error_no_title;
                }
                if (empty($mybb->input['key'])) {
                    $errors[] = $lang->lists_create_error_no_key;
                }
                if (empty($mybb->input['fid'])) {
                    $errors[] = $lang->lists_create_error_no_fid;
                }

                // No errors - insert
                if (empty($errors)) {
                    $lid = $mybb->get_input('lid', MyBB::INPUT_INT);

                    if(!empty($mybb->input['extras'])) {
                        $extralist = implode(",", $mybb->input['extras']);
                    }

                    $edited_list = [
                        "name" => $db->escape_string($mybb->input['name']),
                        "text" => $db->escape_string($mybb->input['text']),
                        "key" => $db->escape_string($mybb->input['key']),
                        "fid" => $db->escape_string($mybb->input['fid']),
                        "filter" => $db->escape_string($mybb->input['filter']),
                        "extras" => $extralist,
                        "sortby" => $db->escape_string($mybb->input['sorted'])
                    ];

                    $db->update_query("lists", $edited_list, "lid='{$lid}'");

                    $mybb->input['module'] = "lists";
                    $mybb->input['action'] = $lang->lists_edited;
                    log_admin_action(htmlspecialchars_uni($mybb->input['name']));

                    flash_message($lang->lists_edited, 'success');
                    admin_redirect("index.php?module=config-lists");
                }

            }
            
            $page->add_breadcrumb_item($lang->lists_create_edit);

            // Editor scripts
            $page->extra_header .= <<<EOF
<link rel="stylesheet" href="../jscripts/sceditor/themes/mybb.css" type="text/css" media="all" />
<script type="text/javascript" src="../jscripts/sceditor/jquery.sceditor.bbcode.min.js?ver=1822"></script>
<script type="text/javascript" src="../jscripts/bbcodes_sceditor.js?ver=1827"></script>
<script type="text/javascript" src="../jscripts/sceditor/plugins/undo.js?ver=1805"></script>
EOF;

            // Build options header
            $page->output_header($lang->lists_name." - ".$lang->lists_create_desc);
            $sub_tabs['lists'] = [
                "title" => $lang->lists_create,
                 "link" => "index.php?module=config-lists",
                "description" => $lang->lists_create_desc
                 
            ];

            $page->output_nav_tabs($sub_tabs, 'lists'); 

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Get the data
            $lid = $mybb->get_input('lid', MyBB::INPUT_INT);
            $query = $db->simple_select("lists", "*", "lid={$lid}");
            $edit_list = $db->fetch_array($query);

            // Build the form
            $form = new Form("index.php?module=config-lists&amp;action=edit_list", "post", "", 1);
            echo $form->generate_hidden_field('lid', $lid);

            $form_container = new FormContainer($lang->lists_create_edit);
            $form_container->output_row(
                $lang->lists_create_name,
                $lang->lists_create_name_desc,
                $form->generate_text_box('name', htmlspecialchars_uni($edit_list['name']))
            );

            $form_container->output_row(
                $lang->lists_create_key,
                $lang->lists_create_key_desc,
                $form->generate_text_box('key', htmlspecialchars_uni($edit_list['key']))
            );

            $text_editor = $form->generate_text_area('text', $edit_list['text'], array(
                    'id' => 'text',
                    'rows' => '25',
                    'cols' => '70',
                    'style' => 'height: 250px; width: 75%'
                )
            );
            $text_editor .= build_mycode_inserter('text');
            $form_container->output_row(
                $lang->lists_create_text,
                $lang->lists_create_text_desc,
                $text_editor,
                'text'
            );

            $fields = [];
            $sortby = [];
            $sortby["username"] = $lang->lists_username;
            $query = $db->simple_select("profilefields", "fid,name");
            while($result = $db->fetch_array($query)) {
                $fid = $result['fid'];
                $fields[$fid] = $result['name'];
                $sortby[$fid] = $result['name'];
            }

            $fields[-1] = $lang->lists_usergroup;
            $fields[-2] = $lang->lists_additionalgroup;
            $fields[-3] = $lang->lists_displaygroup;
            $fields[-4] = $lang->lists_username;

            $form_container->output_row(
                $lang->lists_create_fid, 
                $lang->lists_create_fid_desc, 
                $form->generate_select_box('fid', 
                    $fields, 
                    $edit_list['fid'], 
                    array('id' => 'fid')),
                'fid');

                $form_container->output_row(
                    $lang->lists_create_filter,
                    $lang->lists_create_filter_desc,
                    $form->generate_text_box('filter', htmlspecialchars_uni($edit_list['filter']))
                );

                $extralist = explode(",", $edit_list['extras']);

                $form_container->output_row(
                    $lang->lists_create_extras, 
                    $lang->lists_create_extras_desc, 
                    $form->generate_select_box('extras[]', 
                        $fields, 
                        $extralist, 
                        array('id' => 'fid', 'multiple' => true, 'size' => 5)),
                    'extras');

                    $form_container->output_row(
                        $lang->lists_create_sortby, 
                        $lang->lists_create_sortby_desc, 
                        $form->generate_select_box('sorted', 
                            $sortby, 
                            $edit_list['sortby'], 
                            array('id' => 'sorted')),
                        'sorted');
 
            $form_container->end();
            $buttons[] = $form->generate_submit_button($lang->lists_create_edit);
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();

            exit;
        }
       // Delete entry
       if ($mybb->input['action'] == "delete_list") {
            // Get data
            $lid = $mybb->get_input('lid', MyBB::INPUT_INT);
            $query = $db->simple_select("lists", "*", "lid={$lid}");
            $del_list = $db->fetch_array($query);

            // Error Handling
            if (empty($lid)) {
                flash_message($lang->lists_create_error_invalid, 'error');
                admin_redirect("index.php?module=config-lists");
            }

            // Cancel button pressed?
            if (isset($mybb->input['no']) && $mybb->input['no']) {
                admin_redirect("index.php?module=config-lists");
            }

            if (!verify_post_check($mybb->input['my_post_key'])) {
                flash_message($lang->invalid_post_verify_key2, 'error');
                admin_redirect("index.php?module=config-lists");
            }  // all fine
            else {
                if ($mybb->request_method == "post") {
                    
                    $db->delete_query("lists", "lid='{$lid}'");

                    $mybb->input['module'] = "lists";
                    $mybb->input['action'] = $lang->lists_deleted;
                    log_admin_action(htmlspecialchars_uni($del_list['name']));

                    flash_message($lang->lists_deleted, 'success');
                    admin_redirect("index.php?module=config-lists");
                } else {
                    $page->output_confirm_action(
                        "index.php?module=config-lists&amp;action=delete_list&amp;lid={$lid}",
                        $lang->lists_create_delete
                    );
                }
            }
            exit;
        }
    }
}

function lists_permission($above)
{
	global $mybb, $lang, $form;

	if($above['title'] == $lang->misc && $lang->misc)
	{
		$above['content'] .= "<div class=\"group_settings_bit\">".$form->generate_check_box("showinlists", 1, "Anzeige in automatischen Listen", array("checked" => $mybb->input['showinlists']))."</div>";
	}

	return $above;
}

function lists_permission_commit()
{
	global $mybb, $updated_group;
	$updated_group['showinlists'] = $mybb->get_input('showinlists', MyBB::INPUT_INT);
}