<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'lists.php');

require_once "./global.php";
$lang->load('lists');

add_breadcrumb($lang->lists, "lists.php");

$query = $db->simple_select("lists", "*");
while($list = $db->fetch_array($query)) {
    eval("\$menu_bit .= \"".$templates->get("lists_menu_bit")."\";");
}

eval("\$menu = \"".$templates->get("lists_menu")."\";");

if(!$mybb->input['action']) {
    eval('$page = "'.$templates->get('lists').'";');
    output_page($page);
}

$query = $db->simple_select("lists", "*");
while($list = $db->fetch_array($query)) {
    if($mybb->input['action'] == $list['key']) {
        // let's build queries! 

        if(!preg_match("/-/i", $list['fid'])) {
            $fieldtype = get_fieldtype($list['fid']);
            $fid = "fid".$list['fid'];

            if(!empty($list['filter'])) {
                $filter = $list['filter'];
                $sql_filter = "AND " . $fid . " LIKE '%{$filter}%'";
            }

            // field is text(area), so we want to group entries first
            if($fieldtype == "text" || $fieldtype == "textarea") {
                $query_2 = $db->query("SELECT DISTINCT ". $fid ." 
                FROM ".TABLE_PREFIX."userfields uf
                WHERE ". $fid ." != ''"
                . $sql_filter . "
                ORDER BY ". $fid ." ASC");
                // now get users matching entry
                while($result = $db->fetch_array($query_2)) {
                    $option = $result['name'];
                    $list_bit_user = "";
                    $resfid = $result[$fid];
                    $option = $resfid;
                    $query_3 = $db->query("SELECT ufid, username FROM ".TABLE_PREFIX."userfields uf
                    LEFT JOIN ".TABLE_PREFIX."users u 
                    ON u.uid = uf.ufid
                    LEFT JOIN ".TABLE_PREFIX."usergroups ug
                    ON ug.gid = u.usergroup
                    WHERE uf.". $fid ." = '$resfid'
                    AND ug.showinlists = 1 "
                    . $sql_filter . " 
                    ORDER BY u.username ASC");
                    while($user_result = $db->fetch_array($query_3)) {
                        $extrainfo = "";
                        $profilelink = build_profile_link($user_result['username'], $user_result['ufid']);
                        $listuser = get_user($user_result['ufid']);
                        // any extra information required? 
                        if($list['extras']) {
                            $extrainfo = get_extras($user_result['ufid'], $list['extras']);
                        }
                        eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                    }
                    eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");
                }
            }

            // field is select/multiselect/radio/checkbox, we want to get possible entries first
            if($fieldtype == "select" || $fieldtype == "multiselect" || $fieldtype == "radio" || $fieldtype == "checkbox") {
                $type = $db->fetch_field($db->simple_select("profilefields", "type", "fid = '{$list['fid']}'"), "type");
                $options = explode("\n", $type);
                array_shift($options);
                foreach($options as $option) {
                    $list_bit_user = "";
                    $query_3 = $db->query("SELECT ufid, username FROM ".TABLE_PREFIX."userfields uf
                    LEFT JOIN ".TABLE_PREFIX."users u 
                    ON u.uid = uf.ufid
                    LEFT JOIN ".TABLE_PREFIX."usergroups ug
                    ON ug.gid = u.usergroup
                    WHERE uf.". $fid ." LIKE '%$option%'
                    AND ug.showinlists = 1 "
                    . $sql_filter . " 
                    ORDER BY u.username ASC");
                    while($user_result = $db->fetch_array($query_3)) {
                        $extrainfo = "";
                        $profilelink = build_profile_link($user_result['username'], $user_result['ufid']);
                        $listuser = get_user($user_result['ufid']);
                        // any extra information required? 
                        if($list['extras']) {
                            $extrainfo = get_extras($user_result['ufid'], $list['extras']);
                        }
                        eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                    }
                    eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");
                }
            }

        } else {
            if($list['fid'] != -4) {
                $grouptypes = ["-1" => "usergroup", "-2" => "additionalgroups", "-3" => "displaygroup"];
                $group = $list['fid'];
                $group = $grouptypes[$group];
                if(!empty($list['filter'])) {
                    $filter = $list['filter'];
                    $query_2 = $db->simple_select("usergroups", "title,gid", "title = '{$filter}'");
                } else {
                    $query_2 = $db->simple_select("usergroups", "title,gid", "showinlists = '1'", ["order_by" => "title", "order_dir" => "ASC"]);
                }
                while($result = $db->fetch_array($query_2)) {
                    $list_bit_user = "";
                    $resfid = $result['title'];
                    $resgid = $result['gid'];
                    $option = $result['title'];
                    $query_3 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                        WHERE " . $group ." = '{$resgid}'
                        ORDER BY u.username ASC");
                        while($user_result = $db->fetch_array($query_3)) {
                            $extrainfo = "";
                            $profilelink = build_profile_link($user_result['username'], $user_result['uid']);
                            $listuser = get_user($user_result['uid']);
                            // any extra information required? 
                            if($list['extras']) {
                                $extrainfo = get_extras($user_result['uid'], $list['extras']);
                            }
                            eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                        }
                    eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");
                }
            } else {
                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                WHERE showinlists = '1'
                AND username >= 'A'
                AND username <= 'F'
                ORDER by username ASC");
                $option = "A - F";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                }
                eval("\$list_bit = \"".$templates->get("lists_list_bit")."\";");

                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                WHERE showinlists = '1'
                AND username >= 'G'
                AND username <= 'L'
                ORDER by username ASC");
                $option = "G - L";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                }
                eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");

                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                WHERE showinlists = '1'
                AND username >= 'M'
                AND username <= 'R'
                ORDER by username ASC");
                $option = "M - R";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                }
                eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");

                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                WHERE showinlists = '1'
                AND username >= 'T'
                AND username <= 'Z'
                ORDER by username ASC");
                $option = "T - Z";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                }
                eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");
            }
        }
        eval('$page = "'.$templates->get('lists_list').'";');
        output_page($page);
    }
}

function get_fieldtype($fid) {
    global $db;
    $fieldtype = $db->fetch_field($db->simple_select("profilefields", "type", "fid = '$fid'"), "type");
    if(preg_match("/select/i", $fieldtype) || preg_match("/checkbox/i", $fieldtype) || preg_match("/radio/i", $fieldtype)) {
        $types = explode("\n", $fieldtype);
        $fieldtype = array_shift($types);
    }
    return $fieldtype;
}

function get_extras($uid, $extras) {
    global $db;
    $extralist = explode(",", $extras);
    $grouptypes = ["-1" => "usergroup", "-2" => "additionalgroups", "-3" => "displaygroup"];
    foreach($extralist as $extra) {
        $exfid = "fid".$extra;
        if(!preg_match("/-/i", $extra)) {
            $content = $db->fetch_field($db->simple_select("userfields", $exfid, "ufid = {$uid}"), $exfid);
            $extrainfo .= "&raquo; " . $content . " ";
        } else {
            if($extra != -4) {
                $group = $grouptypes[$extra];
                $groupid = $db->fetch_field($db->simple_select("users", $group, "uid = '{$uid}'"), $group);
                if($extra == -2 && !empty($groupid)) {
                    $additionalgroups = explode(",", $groupid);
                    foreach($additionalgroups as $additionalgroup) {
                        $groupname = $db->fetch_field($db->simple_select("usergroups", "title", "gid = '{$additionalgroup}'"), "title");  
                        $extrainfo .= "&raquo; " . $groupname . " ";
                    }               
                } else {
                    $groupname = $db->fetch_field($db->simple_select("usergroups", "title", "gid = '{$groupid}'"), "title");
                    if(!empty($groupname)) {
                        $extrainfo .= "&raquo; " . $groupname . " ";
                    }
                }
            }
        }   
    }  
    return $extrainfo;
}