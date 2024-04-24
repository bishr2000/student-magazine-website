<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
include('db.php');
require_once('account.php');

class Post extends account{
    

    public function create(){
        $target_dir = "uploads/";
        $target_image = $target_dir . basename($_FILES["image"]["name"]);
        $title = $_POST['title'];
        $text = $_POST['text'];
        $link = $_POST['link'];
        $query = new db();
        $targetfolder = "uploads/";
        $targetfolder = $targetfolder . basename( $_FILES['image']['name']) ;
        if(!isset($_POST['department'])){
            $department = $_SESSION['department'];
        }else{
            $department = $_POST['department'];
        }
        if(move_uploaded_file($_FILES['image']['tmp_name'], $targetfolder)){
            echo "The file ". basename( $_FILES['image']['name']). " is uploaded";
        }else{
            echo "Problem uploading file";
        }
        if($_SESSION['type'] == "professor"){
            $query->query("INSERT INTO `lectures`(`link`, `account_id`) VALUES(?,?)", $link, $_SESSION['id']);
            $query->query("INSERT INTO `post`(`Title`, `Content`,`image`,`Department`,`DatePublished`,`accountID`) VALUES(?,?,?,?,?,?);"
            ,$title, $text, $target_image, $department, date("Y/m/d H:i:s"), $_SESSION['id']);
        }else
            $query->query("INSERT INTO `post`(`Title`, `Content`,`image`,`Department`,`DatePublished`,`accountID`) VALUES(?,?,?,?,?,?);"
            ,$title, $text, $target_image, $department, date("Y/m/d H:i:s"), $_SESSION['id']);
        $query->query("UPDATE `viewcounter` SET  `views` = `views`+1 WHERE `id` = '1'");
        echo "<script>window.location = 'main.php'</script>";
    }

    public function print($department){
            $query = new db();
            if($department == ""){
                $sql = "SELECT * FROM post";
            }else
                $sql = "SELECT * FROM `post` WHERE `Department` = '$department';";
            
            $result = $query->query($sql)->fetchAll();
            
            if($result){
                for($i = $query->query($sql)->numRows() -1; $i >= 0 ; $i--){
                    $sql2 = "SELECT * FROM `accounts` WHERE `id` =" . $result[$i]['accountID'] . ";"; 
                    $result2 = $query->query($sql2)->fetchArray();
                    ?>
                    <div class="row">
                    <article>
                        <!-- Post header-->
                        <header class="mb-4">
                            <!-- Post title-->
                            <h1 class="fw-bolder mb-1"><?php echo $result[$i]['Title'] ?></h1>
                            <!-- Post meta content-->
                            <div class="text-muted fst-italic mb-2">Posted on <?php echo $result[$i]['DatePublished'] ?> By <a href = 'profile.php?pf=<?php echo $result2['id']?>'>  <?php echo $result2['f_name']. " " . $result2['l_name']; ?></a></div>
                            <!-- Post categories-->
                            <a class="badge bg-secondary text-decoration-none link-light" href="#!"><?php echo $result[$i]['Department']; ?></a>
                        </header>
                        <!-- Preview image figure-->
                        <figure class="mb-4"><img  style="width:900px;height:400px;" class="img-fluid rounded" src="../<?php echo $result[$i]['image'];?>" alt="..." /></figure>
                        <!-- Post content-->
                        <section class="mb-5">
                            <?php
                                if(strlen($result[$i]['Content']) > 500)
                                    echo substr($result[$i]['Content'], 0, 500) . "...<a>read more</a>";
                                else 
                                    echo $result[$i]['Content'];
                            ?>
                            <div class = "row justify-content-end">
                                <p class="col fs-5 mb-4"><a  href = 'post_details.php?id=<?php echo $result[$i]['PostID']; ?>' class='btn btn-sm btn-success col-3'>Read more</a></p>
                                <p class="col fs-5 mb-4"><button class = "eminem btn btn-danger col-3"  id="<?php echo $result[$i]['PostID']; ?>" data-toggle="modal" data-target="#reason"  style = "margin-left: 20px;" name = "report">Report</button></p>
                            </div>
                        </section>
                    </article>    
                </div>
                <?php
                } 
            }
        
    }
    public function search($text){
        $q = new db();
        
        $query = "SELECT * FROM post WHERE ";
        $query_fields = Array();
        $sql = "SHOW COLUMNS FROM post";
        $columnlist = $q->query($sql)->fetchAll();
        $data = Array();
        for($i = 1; $i <= 2; $i++){
            $data[] = $columnlist[$i]['Field'] . " like('%". $text . "%')";
        }
        $query .= implode(" OR ", $data); 
        $result = $q->query($query)->fetchAll();
        if($result){
            for($i = $q->query($query)->numRows() -1; $i >= 0 ; $i--){
                $sql2 = "SELECT * FROM `accounts` WHERE `id` =" . $result[$i]['accountID'] . ";"; 
                $result2 = $q->query($sql2)->fetchArray();
                ?>
                    <div class="row">
                    <article>
                        <!-- Post header-->
                        <header class="mb-4">
                            <!-- Post title-->
                            <h1 class="fw-bolder mb-1"><?php echo $result[$i]['Title'] ?></h1>
                            <!-- Post meta content-->
                            <div class="text-muted fst-italic mb-2">Posted on <?php echo $result[$i]['DatePublished'] ?> By <a href = 'profile.php?pf=<?php echo $result2['id']?>'>  <?php echo $result2['f_name']. " " . $result2['l_name']; ?></a></div>
                            <!-- Post categories-->
                            <a class="badge bg-secondary text-decoration-none link-light" href="#!"><?php echo $result[$i]['Department']; ?></a>
                        </header>
                        <!-- Preview image figure-->
                        <figure class="mb-4"><img  style="width:900px;height:400px;" class="img-fluid rounded" src="../<?php echo $result[$i]['image'];?>" alt="..." /></figure>
                        <!-- Post content-->
                        <section class="mb-5">
                            <?php
                                if(strlen($result[$i]['Content']) > 500)
                                    echo substr($result[$i]['Content'], 0, 500) . "...<a>read more</a>";
                                else 
                                    echo $result[$i]['Content'];
                            ?>
                            <div class = "row justify-content-end">
                                <p class="col fs-5 mb-4"><a  href = 'post_details.php?id=<?php echo $result[$i]['PostID']; ?>' class='btn btn-sm btn-success col-3'>Read more</a></p>
                                <p class="col fs-5 mb-4"><button class = "eminem btn btn-danger col-3"  id="<?php echo $result[$i]['PostID']; ?>" data-toggle="modal" data-target="#reason"  style = "margin-left: 20px;" name = "report">Report</button></p>
                            </div>
                        </section>
                    </article>    
                </div>
                <?php
    }
}else{
    echo "<h3>no results were found</h3>";
}
}
}