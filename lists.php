<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'lists.php');

require_once "./global.php";
$lang->load('lists');

if($mybb->settings['lists_uids']) {
$listsuids = $mybb->settings['lists_uids'];
} else {
    $listsuids = 0;
}

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

        // Format Entries
        require_once MYBB_ROOT."inc/class_parser.php";
        $parser = new postParser;
        $parser_options = array(
            "allow_html" => 1,
            "allow_mycode" => 1,
            "allow_smilies" => 1,
            "allow_imgcode" => 1
        );

        $list['text'] = $parser->parse_message($list['text'], $parser_options);   

        // let's build queries! 

        // special sort method?
        if(!empty($list['sortby'])) {
            $sort = $list['sortby'];
            if(!preg_match("/username/i", $list['sortby'])) {
                $sort = "fid" . $list['sortby'];
            }
        } if(empty($list['sortby'])) { $sort = "username"; }

        // filter by profilefield
        if(!preg_match("/-/i", $list['fid'])) {
            $fieldtype = get_fieldtype($list['fid']);
            $fid = "fid".$list['fid'];

            // any filters? build query
            if(!empty($list['filter'])) {
                $filter = $db->escape_string($list['filter']); 
                $sql_filter = "AND " . $fid . " LIKE '%{$filter}%'";
                if($filter == "male") {
                    $sql_filter = "AND " . $fid . " LIKE '%{$filter}%'
                    AND " . $fid . " != 'female'";
                }
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
                    $list_bit_user = "";
                    $resfid = $db->escape_string($result[$fid]);
                    // no headline if we got a filter, because we already know what we're seeing, right?
                    if(!empty($filter)) {
                        $option = "";
                    } else {
                    $option = $resfid; }
                    $query_3 = $db->query("SELECT ufid, username FROM ".TABLE_PREFIX."userfields uf
                    LEFT JOIN ".TABLE_PREFIX."users u 
                    ON u.uid = uf.ufid
                    LEFT JOIN ".TABLE_PREFIX."usergroups ug
                    ON ug.gid = u.usergroup
                    WHERE uf.". $fid ." = '$resfid'
                    AND ug.showinlists = 1 "
                    . $sql_filter . " 
                    AND ufid NOT IN($listsuids)
                    ORDER BY " . $sort ." ASC");
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
                    // does this option match our filter?
                    if(preg_match("/$filter/i", $option)) {
                        $genderfilter = "";
                        $option = $db->escape_string($option);
                        if($option == "Male" || $option == "male") {
                            $genderfilter = "AND " . $fid . " NOT LIKE '%female%'";
                        }
                        $list_bit_user = "";
                        // get users matching filter
                        $query_3 = $db->query("SELECT ufid, username FROM ".TABLE_PREFIX."userfields uf
                        LEFT JOIN ".TABLE_PREFIX."users u 
                        ON u.uid = uf.ufid
                        LEFT JOIN ".TABLE_PREFIX."usergroups ug
                        ON ug.gid = u.usergroup
                        WHERE uf.". $fid ." LIKE '%$option%'
                        AND ug.showinlists = 1 "
                        . $sql_filter . " 
                        AND ufid NOT IN($listsuids) "
                        . $genderfilter . " 
                        ORDER BY " . $sort . " ASC");
                        while($user_result = $db->fetch_array($query_3)) {
                            $extrainfo = "";
                            $profilelink = build_profile_link($user_result['username'], $user_result['ufid']);
                            $listuser = get_user($user_result['ufid']);
                            // any extra information required? 
                            if($list['extras']) {
                                $extrainfo = get_extras($user_result['ufid'], $list['extras']);
                            }
                            // no headline if we got a filter, because we already know what we're seeing, right?
                            if(!empty($filter)) {
                                $option = "";
                            }
                            eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                        }
                        eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");
                    }
                }
            }

        } 
        // filter is not a profilefield
        else {
            // we're filtering by usergroup
            if($list['fid'] != -4) {
                // get possible grouptypes
                $grouptypes = ["-1" => "usergroup", "-2" => "additionalgroups", "-3" => "displaygroup"];
                $group = $list['fid'];
                $group = $grouptypes[$group];
                // if there's a filter we only need one group
                if(!empty($list['filter'])) {
                    $filter = $list['filter'];
                    $query_2 = $db->simple_select("usergroups", "title,gid", "title LIKE '%{$filter}%'");
                } else {
                    // if not, we need to get all groups that are supposed to show in lists
                    $query_2 = $db->simple_select("usergroups", "title,gid", "showinlists = '1'", ["order_by" => "title", "order_dir" => "ASC"]);
                }
                while($result = $db->fetch_array($query_2)) {
                    $list_bit_user = "";
                    $resfid = $result['title'];
                    $resgid = $result['gid'];
                    // no headline if we got a filter, because we already know what we're seeing, right?
                    if(!empty($filter)) {
                        $option = "";
                    } else {
                    $option = $result['title']; }
                    // get all users in this group
                    $query_3 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                        LEFT JOIN ".TABLE_PREFIX."userfields uf 
                        ON u.uid = uf.ufid
                        WHERE " . $group ." = '{$resgid}'
                        AND uid NOT IN($listsuids)
                        ORDER BY " . $sort ." ASC");
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
            } 
            // we're filtering by username
            else {
                // alphabetically ...
                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                LEFT JOIN ".TABLE_PREFIX."userfields uf 
                ON u.uid = uf.ufid
                WHERE showinlists = '1'
                AND " . $sort . " >= 'A'
                AND " . $sort . " <= 'F'
                AND uid NOT IN($listsuids)
                ORDER by " . $sort . " ASC");
                $option = "A - F";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    if(!empty($extrainfo)) {
                        eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                    }
                }
                eval("\$list_bit = \"".$templates->get("lists_list_bit")."\";");

                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                LEFT JOIN ".TABLE_PREFIX."userfields uf 
                ON u.uid = uf.ufid
                WHERE showinlists = '1'
                AND " . $sort . " >= 'G'
                AND " . $sort . " <= 'L'
                AND uid NOT IN($listsuids)
                ORDER by " . $sort . " ASC");
                $option = "G - L";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    if(!empty($extrainfo)) {
                        eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                    }
                }
                eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");

                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                LEFT JOIN ".TABLE_PREFIX."userfields uf 
                ON u.uid = uf.ufid
                WHERE showinlists = '1'
                AND " . $sort . " >= 'M'
                AND " . $sort . " <= 'S'
                AND uid NOT IN($listsuids)
                ORDER by " . $sort . " ASC");
                $option = "M - S";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    if(!empty($extrainfo)) {
                        eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                    }
                }
                eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");

                $query_2 = $db->query("SELECT uid, username FROM ".TABLE_PREFIX."users u
                LEFT JOIN ".TABLE_PREFIX."usergroups ug
                ON u.usergroup = ug.gid
                LEFT JOIN ".TABLE_PREFIX."userfields uf 
                ON u.uid = uf.ufid
                WHERE showinlists = '1'
                AND " . $sort . " >= 'T'
                AND " . $sort . " <= 'Z'
                AND uid NOT IN($listsuids)
                ORDER by " . $sort . " ASC");
                $option = "T - Z";
                $list_bit_user = "";
                while($result = $db->fetch_array($query_2)) {
                    $profilelink = build_profile_link($result['username'], $result['uid']); 
                    $listuser = get_user($result['uid']);
                    // any extra information required? 
                    if($list['extras']) {
                        $extrainfo = get_extras($result['uid'], $list['extras']);
                    }
                    if(!empty($extrainfo)) {
                        eval("\$list_bit_user .= \"".$templates->get("lists_list_bit_user")."\";");
                    }
                }
                eval("\$list_bit .= \"".$templates->get("lists_list_bit")."\";");
            }
        }
        eval('$page = "'.$templates->get('lists_list').'";');
        output_page($page);
    }
}

// get field type of profilefield
function get_fieldtype($fid) {
    global $db;
    $fieldtype = $db->fetch_field($db->simple_select("profilefields", "type", "fid = '$fid'"), "type");
    if(preg_match("/select/i", $fieldtype) || preg_match("/checkbox/i", $fieldtype) || preg_match("/radio/i", $fieldtype)) {
        // if type is list, first entry is fieldtype, so take that
        $types = explode("\n", $fieldtype);
        $fieldtype = array_shift($types);
    }
    return $fieldtype;
}

// get extra information
function get_extras($uid, $extras) {
    global $db;
    $extralist = explode(",", $extras);
    $grouptypes = ["-1" => "usergroup", "-2" => "additionalgroups", "-3" => "displaygroup"];
    foreach($extralist as $extra) {
        $exfid = "fid".$extra;
        // extra information is profilefield
        if(!preg_match("/-/i", $extra)) {
            $content = $db->fetch_field($db->simple_select("userfields", $exfid, "ufid = {$uid}"), $exfid);
            if(!empty($content)) {
                $extrainfo .= "&raquo; " . $content . " ";
            }
        } 
        // it's either usergroup or username
        else {
            // it's usergroup. username would be useless
            if($extra != -4) {
                // get groups user is in
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