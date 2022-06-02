<?php
/*
 * Plugin Name: WRDSB School Contact Information
 * Plugin URI: https://github.com/wrdsb/wordpress-plugin-school-contact/
 * Description: Manage your School Information!
 * Version: 0.6.0
 * Author: Suzanne Carter
 * GitHub Plugin URI: wrdsb/wordpress-plugin-school-contact
 * GitHub Branch: master
 */

//Define menu
add_action('admin_menu', 'wrdsb_school_info_manager');

//Add menu to admin panel
function wrdsb_school_info_manager() 
{
    add_submenu_page( 'themes.php', 'manage_school_information', 'Manage School Info', 1, 'manage_school_information', 'manage_school_information');
}
    
function manage_school_information() 
{
    echo<<<END
    <div class="wrap">
    <h2>School Information Manager</h2>
    <p>This information is displayed in the footer of every page of your school website.</p>
END;
    build_school_info_table($_POST);
    echo '</div>';
}

function get_school_code() 
{
    $parsed_url = parse_url(site_url());
      $host = explode('.', $parsed_url['host']);
    $school_code = $host[0];

    // what if we're in the admin section?
    if ($school_code === 'schools')
    {
        $whereami = get_bloginfo('url');
        $whereami = str_replace('http://schools.wrdsb.ca/','',$whereami);
        $school_code = $whereami;
    }

    $extended_school_code = $school_code;

    switch ($extended_school_code)
    {
        case 'wplabs':
            $school_code = 'xxx';
            break;

        case 'chinese':
            $school_code = 'chi';
            break;

        case 'german':
            $school_code = 'ger';
            break;

        case 'greek':
            $school_code = 'gre';
            break;

        case 'serbian':
            $school_code = 'ser';
            break;

        case 'gnss':
            $school_code = 'gns';
            break;

        case 'dsps':
            $school_code = 'dps';
            break;

        default:
            $school_code = $school_code;
            break;
    }

    return $school_code;
}

function build_school_info_table($post_data)
{
    global $wpdb;
    $school_code = get_school_code();
    $commit = array();
    if (isset($post_data))
        {
        foreach($post_data as $key=>$p)
            {
            $commit[$key] = $p;
            }
        $uid = substr($key,0,strpos($key,'_',0));
        $link_key = $key;
        $link_value = $pd;
        $wpdb->update(schools_schools,$commit,array('field_school_code_value' => $school_code));
        }

    $titles = array(
        'field_school_name'=>'School Name',
        'field_school_type_value'=>'School Group',
        'field_school_street_value'=>'Address',
        'field_school_city_value'=>'City',
        'field_school_postalcode_value'=>'Postal Code',
        'field_school_phone_value'=>'Phone',
        'field_school_fax_value'=>'Fax',
        'field_school_organization_value'=>'School Type',
        'field_school_attendance_line_value'=>'Attendance Phone',
        'field_school_hours_value'=>'School Hours',
        'field_school_office_hours_value'=>'Office Hours',
        'field_school_website_value'=>'Website',
        'field_school_municipality_value'=>'Municipality',
        'field_school_messagebox_value'=>'field_school_messagebox_value',
        'field_school_break_times_value'=>'Break Times',
        'field_school_code_value'=>'School Code'
        );
    
    $list = $wpdb->get_results( "SELECT * FROM schools_schools where field_school_code_value LIKE '$school_code'" );
    ?>
    <form id="form1" name="form1" method="post" action="">
    <table>
    <?php
    if (count($list) > 0)
    {
        foreach ($list['0'] as $key=>$l)
        {
            if ($key == 'field_school_name' AND $l == "" )
            {
                $l = get_bloginfo('name');
            }
            if ($key == 'field_school_name' OR $key == 'field_school_type_value' OR $key == 'field_school_website_value' OR $key == 'field_school_code_value')
            {
                $edit = 'readonly="readonly"';	
            }
            echo '<tr><td><label for="'.$key.'"><strong>'.$titles[$key].'</strong></label></td>';
            echo '<td><input type="text" size="60" name="'.$key.'" id="'.$key.'" value="'.$l.'"'.$edit.'/></td></tr>';	
            $edit = "";
        }
        ?>
    </table>
    <input type="submit" value="Update your School Information">
    </form>
    <?php
    }
    else
    {
        echo 'We cannot find the matching school information in our database. Please put in a ticket through <a href="https://itservicedesk.wrdsb.ca/ITServiceDesk.WebAccess/ss/object/create.rails?class_name=IncidentManagement.Incident&amp;lifecycle_name=NewProcess21&amp;object_template_name=NewTemplate28" target="_blank" title="WordPress Support">ITService Desk</a> for assistance.';
    }
}

function wrdsb_school_info_display() {
    global $wpdb;
    $school_code = get_school_code();
    $list = $wpdb->get_results( "SELECT * FROM schools_schools where field_school_code_value LIKE '$school_code'" );

    // if null (not a school in the list), display
    if (is_null($list[0])) {
echo<<<END
    <h1>Waterloo Region District School Board</h1>
    <address>
    <p>51 Ardelt Avenue<br />
    Kitchener, ON N2C 2R5</p>

    <p>Switchboard: 519-570-0003<br />
    <a href="http://www.wrdsb.ca/about-the-wrdsb/contact/">Contact the WRDSB</a></p>
    
    <p><a href="http://www.wrdsb.ca/about-the-wrdsb/contact/website-feedback/">Website Feedback Form</a></p>
    </address>
END;
    } else {
        // cannot be null
        $name         = $list[0]->field_school_name;
        $street       = $list[0]->field_school_street_value;
        $city         = $list[0]->field_school_city_value;
        $postalcode   = $list[0]->field_school_postalcode_value;
        $phone        = $list[0]->field_school_phone_value ;
        $school_hours = $list[0]->field_school_hours_value; 

        // may be null (Language Schools and Exemplars)
        $fax          = $list[0]->field_school_fax_value;
        $attendance   = $phone . ', press 1';
        //$list[0]->field_school_attendance_line_value;
        $office_hours = $list[0]->field_school_office_hours_value;
        $breaks       = $list[0]->field_school_break_times_value;
        
        // generated
        $email        			= $school_code.'@wrdsb.ca';
        $attendance_email       = $school_code.'-attendance@wrdsb.ca';

        // School Name
        echo '<h1>'.$name.'</h1>';

        // School Address and Map
        echo '<address><p>'.$street.'<br />'.$city.', ON '.$postalcode.' <a target="_blank" href="http://maps.google.com/maps?f=q&hl=en&q='.$street.'+'.$city.'+Ontario">(Map)</a></p>';

        // School Phone and Fax and General Inbox Information
        echo '<p>Phone: '.$phone. ', press 3';
        if ($fax != '') {
            echo '<br />
            Fax: '.$fax;
        }

        if ($school_code !== 'ERL') {
            echo '<br />General email: <a href="mailto:'.$email.'">'.$email.'</a>';
        }

        // Attendance information
        echo '<br />Attendance:';
        if ($attendance != '') {
            // School Attedance Phone
            echo '<br />&nbsp; &bull; '.$attendance;
        }

        if ($school_code !== 'ERL') {
            // School Attendance Email Address
            echo '<br />&nbsp; &bull; <a href="mailto:'.$attendance_email.'">email attendance</a>';
        }

        // School Email Address
        
        echo '<br /><a href="/about/staff-list/">Staff Contact Information</a>';
        // echo '<br />Email: <a href="mailto:'.$email.'">'.$email.'</a>';
        echo '</p>';

        // School Hours
        echo '<p>School Hours: '.$school_hours;

        if ($office_hours != '') {
            echo '<br />
            Office Hours: '.$office_hours;
        }

        if ($breaks != '') {
            echo '<br />
            Break Times: '.$breaks;
        }
        
        echo '</p></address>';
        echo '<p><a href="http://www.wrdsb.ca/about-the-wrdsb/contact/website-feedback/">Website Feedback Form</a></p>';
    }
}
