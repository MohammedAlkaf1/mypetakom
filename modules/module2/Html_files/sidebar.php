<link rel="stylesheet" href="../Styles/sidebar.css">
<?php
// If we're NOT inside 'Html_files', add 'Html_files/' to the path
$in_html_folder = basename(__DIR__) === 'Html_files';
$prefix = $in_html_folder ? '' : 'Html_files/';
?>

<aside class="sidebar">
  <ul>
    <li><a href="mypetakom/dashboard/advisor_dashboar">Dashboard</a></li>
    <li><a href="mypetakom/modules/module1/Html_files/event_advisor">Events</a></li>
    <li><a href="#">Other Link</a></li>
    <li><a href="#">Another Link</a></li>
  </ul>
</aside>
