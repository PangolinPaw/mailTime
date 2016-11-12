<html>
<head>
    <title>mailTime</title>
    <link rel="icon" href="images/calendar.ico" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">

</head>

<body>

<?php
    // === CONNECT TO DATABASE ===
    class MyDB extends SQLite3
    {
        function __construct()
        {
            $this->open('../data/mailTime_data.db'); 
        }
    }
    $db = new MyDB();
    if(!$db)
    {
        echo '<div class="dbStatus">Database Connection Error: ' . $db->lastErrorMsg() . '</div>';
    }

    date_default_timezone_set("GMT"); // Set timezone for all date/time operations

    // Limit no. of customer records retrieved
    $start_from = intval($_GET['limit']);

    // === READ FROM DATABASE ===
    // Get customer data
    $cust_query = $db->query("SELECT * 
                                FROM customers
                                WHERE CUSTOMER_ID>" . $start_from . " LIMIT 100");
   

    // === WRITE TO DATABASE ===
    if (isset($_POST['edit_cust_id'])) {

        $single_cust_query = $db->query("SELECT * 
                                FROM customers
                                WHERE CUSTOMER_ID=" . intval(($_POST['edit_cust_id']) ));

        $single_cust_data = $single_cust_query->fetchArray();

        $cust_id = intval($single_cust_data[0]);
        $email = $single_cust_data[1];
        $firstname = $single_cust_data[2];
        $surname = $single_cust_data[3];
        $data_1 = $single_cust_data[4];
        $data_2 = $single_cust_data[6];
        $data_3 = $single_cust_data[8];
        $opt_out = $single_cust_data[10];

        if ($opt_out == "TRUE") {
            $radio_yes = "checked";
            $radio_no = "";
        } else {
            $radio_no = "checked";
            $radio_yes = "";
        }

        echo '  <div id="mask" class="mask">
                    <div class="create_task_popup">
                        <div class="section_heading">Edit Customer</div>
                        <br />
                        <form id="save_cust_details" action="customer.php?limit=' . $start_from . '" method="post">
                            <table class="task_list">
                                <tr>
                                    <th style="width:6em;">ID</th>
                                    <td>#' . $cust_id . '</td>
                                </tr>
                                <tr>
                                    <th>First name</th>
                                    <td><input type="text" name="new_firstname" value="' . $firstname . '"></td>
                                </tr>
                                <tr>
                                    <th>Surname</th>
                                    <td><input type="text" name="new_surname" value="' . $surname . '"></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><input type="text" name="new_email" value="' . $email . '"></td>
                                </tr>
                                <tr>
                                    <th>Field 1</th>
                                    <td><input type="text" name="new_data_1" value="' . $data_1 . '"></td>
                                </tr>
                                <tr>
                                    <th>Field 2</th>
                                    <td><input type="text" name="new_data_2" value="' . $data_2 . '"></td>
                                </tr>
                                <tr>
                                    <th>Field 3</th>
                                    <td><input type="text" name="new_data_3" value="' . $data_3 . '"></td>
                                </tr>
                                <tr>
                                    <th style="background-color: #F0777F;">Opted out</th>
                                    <td><input type="radio" name="new_opt-out" value="TRUE" ' . $radio_yes . '>Yes<input type="radio" name="new_opt-out" value="FALSE" ' . $radio_no . '>No</td>
                                </tr>
                            </table>
                            <br />
                            <input type="hidden" name="cust_id" value=" ' . $cust_id . '">
                            <div class="submit_button" onclick="document.getElementById(\'save_cust_details\').submit();">Save Changes</div>
                            <a href="customer.php" style="text-decoration: none;"><div class="submit_button cancel_button">Cancel</div></a>
                        </form>
                    </div>
                </div>';
    }

    if (isset($_POST['new_firstname'])) {
        $db->exec('UPDATE customers
                    SET FIRST_NAME="' . $_POST["new_firstname"] . '", LAST_NAME="' . $_POST["new_surname"] . '", EMAIL="' . $_POST["new_email"] . '",
                    DATA_FIELD_1="' . $_POST["new_data_1"] . '", DATA_FIELD_2="' . $_POST["new_data_2"] . '", DATA_FIELD_3="' . $_POST["new_data_3"] . '",
                    OPT_OUT="' . $_POST["new_opt-out"] . '"
                    WHERE CUSTOMER_ID=' . $_POST['cust_id']);
    }

    if (isset($_FILES['csv'])) {
        $info = pathinfo($_FILES['csv']['name']);
        $ext = $info['extension']; // get the extension of the file
        echo "test";

        if ($ext == "csv") {
            $newname = "latest_customer_upload.".$ext; 

            $target = '../data/uploads/'.$newname;
            move_uploaded_file( $_FILES['csv']['tmp_name'], $target);
            echo '<div class="overlay_message bad">Upload Customers:<br />The file "' . $_FILES['csv']['name'] . '" was uploaded successfully. Please allow a few minutes for processing.</div>';
        } else {
            echo '<div class="overlay_message bad">Upload Customers:<br />The file "' . $_FILES['csv']['name'] . '" could not be read. Please ensure it is in .csv format.</div>';
        }

        
    }
?>

    <div class="container">
        <div class="header">
            <a href="index.php"><h1>mailTime</h1></a>
            <ul class="nav">
                <li><a href="index.php">Schedule</a></li>
                <li><a href="customer.php">Customers</a></li>
                <li><a href="help.html">Help</a></li>
            </ul>
        </div>

        <br />

        <div class="subsection">
            <div class="section_heading">Add Customer</div>
            <form id="create_single" action="customer.php?limit=<?php echo $start_from; ?>" method="post">
                <table class="task_list">
                    <tr>
                        <th>First name</th>
                        <th>Surname</th>
                        <th>Email</th>
                        <th>Field 1</th>
                        <th>Field 2</th>
                        <th>Field 3</th>
                    </tr>
                    <tr>
                        <td><input type="text" name="create_firstname"></td>
                        <td><input type="text" name="create_surname"></td>
                        <td><input type="text" name="create_email"></td>
                        <td><input type="text" name="create_data_1"></td>
                        <td><input type="text" name="create_data_2"></td>
                        <td><input type="text" name="create_data_3"></td>
                    </tr>
                </table>
                <div class="submit_button" style="float:right; width:10em; margin-right: 1em;" onclick="document.getElementById('create_single').submit();">Save</div>
            </form>
            <br />
        </div>

        <br />

        <div class="subsection">
            <div class="section_heading">Upload Customers</div>
                <p style="margin-bottom: 0;">You may upload customer details in bulk from a comma-separated values file (.csv). This file must contain one customer per row and a row of headings as shown in the table below (the second row of the example table details what type of data each column may contain):</p>
                <table class="task_list">
                    <tr>
                        <th>First name</th>
                        <th>Surname</th>
                        <th>Email</th>
                        <th>Field 1</th>
                        <th>Field 2</th>
                        <th>Field 3</th>
                        <th>Opt-out status</th>
                    </tr>
                    <tr>
                        <td>Text</td>
                        <td>Text</td>
                        <td>Text</td>
                        <td>Text</td>
                        <td>Text</td>
                        <td>Text</td>
                        <td>"TRUE" (opted-out) or "FALSE" (not opted-out)</td>
                    </tr>
                </table>
                <form id="upload_multiple" action="customer.php?limit=<?php echo $start_from; ?>" method="post" enctype="multipart/form-data">
                    <div class="upload_form">
                        <input type="file" name="csv">
                        <div class="submit_button" style="float:right; width:10em; margin-right: 1em;" onclick="document.getElementById('upload_multiple').submit();">Upload</div>
                    </div>
                </form>
                <br />
                <br />
        </div>

        <br />

        <div class="subsection">
            <div class="section_heading">Edit Customers</div>
            <div class="scroll" style="height: 15em;">
                <table class="task_list">
                        <tr>
                            <th></th>
                            <th>First name</th>
                            <th>Surname</th>
                            <th>Email</th>
                            <th>Field 1</th>
                            <th>Field 2</th>
                            <th>Field 3</th>
                        </tr>
                        <?php
                            while ($cust_data = $cust_query->fetchArray())
                            {
                                $cust_id = $cust_data[0];
                                $email = $cust_data[1];
                                $firstname = $cust_data[2];
                                $surname = $cust_data[3];
                                $data_1 = $cust_data[4];
                                $data_2 = $cust_data[6];
                                $data_3 = $cust_data[8];
                                $opt_out = $cust_data[10];

                                if ($opt_out == "TRUE") {
                                    echo '<tr class="opt_out">';
                                    $email = "<span style='text-decoration: line-through;'>" . $email . "</span>";
                                } else {
                                    echo '<tr>';
                                }

                                echo '      <td>#' . $cust_id . '</td>
                                            <td>' . $firstname . '</td>
                                            <td>' . $surname . '</td>
                                            <td>' . $email . '</td>
                                            <td>' . $data_1 . '</td>
                                            <td>' . $data_2 . '</td>
                                            <td>' . $data_3 . '</td>
                                            <td>
                                                <form id="edit_customer_' . $cust_id . '" action="customer.php?limit=' . $start_from . '" method="post" style="margin: 0; padding: 0;">
                                                    <input type="hidden" value="' . $cust_id . '"  name="edit_cust_id">
                                                    <a href="#"onclick="document.getElementById(\'edit_customer_' . $cust_id . '\').submit();">Edit</a>
                                                </form>
                                            </td>
                                        </tr>';
                            }
                        ?>
                </table>
                </div><!--scroll -->
                <p><?php if ($start_from>0) { echo '<a href="customer.php?limit=' . ($start_from-100) . '">&#8592;</a> ';} ?> <?php echo $start_from + 1; ?> to <?php echo $start_from + 100; ?> <a href="customer.php?limit=<?php echo $start_from + 100; ?>">&#8594;</a>
        </div>

        <br />

    </div> <!-- container -->
</body>
</html>
