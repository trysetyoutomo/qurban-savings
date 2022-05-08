
<?php 
$servername = "localhost";
$username = "*********";
$password = "*********";
$dbname = "*********";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "select id_user,nama_lengkap,ifnull(sum(tabung.total),0) as total,tabung.bukti from user left join tabung on user.id = tabung.id_user group by user.id order by total desc";
$data = mysqli_query($conn, $sql);



if (isset($_POST['simpan'])){

$target_dir = "img/";
// $target_file = $target_dir . md5(basename($_FILES["gambar"]["name"]).date("YmdHis"));
$target_file = $target_dir . basename($_FILES["gambar"]["name"]);
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
$lastName = md5(basename($_FILES["gambar"]["name"]).date("YmdHis")).$imageFileType;
$target_file = $target_dir . $lastName;


    if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["gambar"]["name"])). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }   

    
    $sql = "INSERT INTO tabung (id_user, total, bukti,tanggal_masuk) VALUES ('$_POST[id_user]', '$_POST[total]', '$lastName','".date("Y-m-d H:i:s")."')";

    if ($conn->query($sql) === TRUE) {
        sleep(2);
        header("location:https://35utech.com/tabungan-qurban");
        // echo "<div class='alert alert-success'>Berhasil disimpan! <a href='https://35utech.com/tabungan-qurban'>Kembali</a> </div>";
    }


    // echo "simpan";
    exit;

}

if (isset($_REQUEST['tabungandetail'])){
    $sql = "select id_user,nama_lengkap, total,tabung.bukti,tabung.tanggal_masuk from user inner join tabung on user.id = tabung.id_user where id_user = {$_REQUEST[tabungandetail]} order by total desc";
    $data = mysqli_query($conn, $sql);
    $array = [];
    while($row = mysqli_fetch_assoc($data)) { 
        array_push($array,[
            "tanggal_masuk"=>date("d M Y ",strtotime($row['tanggal_masuk'])),
            "total"=>number_format($row['total']),
            "bukti"=>"https://35utech.com/tabungan-qurban/img/".$row['bukti']
        ]);
        // $array[] = $row['tanggal_masuk'];
        // $array["tanggal_masuk"] = $row['tanggal_masuk'];

    }
    echo json_encode($array);

    exit;
}

// print_r($data);
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

   <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet">

    <title>Tabungan Qurban IKRAR</title>
    <style>
        body{
            font-family: 'Roboto', sans-serif; 
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                searching: false,
               "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
               "lengthChange":false
            });
            
            $(document).on("submit","#form-tabung",function(e){
                let c = confirm("Yakin Simpan ?");
                if (c!=true){
                    return false;
                }
                // $("#simpan").attr("disabled",true);
                $("#simpan").val("Sedang Menyimpan ..");
            })
            
            $(document).on("click",".copy-bsi",function(e){
                 /* Get the text field */
                var copyText = document.getElementById("input-bsi");

                /* Select the text field */
                copyText.select();
                copyText.setSelectionRange(0, 99999); /* For mobile devices */

                /* Copy the text inside the text field */
                document.execCommand("copy");

                /* Alert the copied text */
                alert("Berhasil dicopy!!");
            });
            
              $(document).on("click",".copy-bni",function(e){
                  /* Get the text field */
                var copyText = document.getElementById("input-bni");
                
                /* Select the text field */
                copyText.select();
                copyText.setSelectionRange(0, 99999); /* For mobile devices */
                
                /* Copy the text inside the text field */
                document.execCommand("copy");
                
                /* Alert the copied text */
                alert("Berhasil dicopy!!");
            });

            $(document).on("click",".btn-input-tabungan",function(e){
                let c = prompt("Masukan Kode Bendahara :");
                if (c == "x387"){
                    $("#staticBackdropInput").modal("show");
                }
            });


            $(document).on("click",".btn-detail",function(e){
                let id_user = $(this).data("id-user");
                $.ajax({
                    url : "https://35utech.com/tabungan-qurban/index.php?tabungandetail="+id_user,
                    success : function(data){
                        let x = JSON.parse(data);
                        if (x.length > 0){

                            let no = 1;
                            $("#tbody").html("");
                            $.each(x,function(i,v){
                                $("#tbody").append("<tr><td>"+no+"</td><td>"+v.tanggal_masuk+"</td><td>"+v.total+"</td><td><a href='"+v.bukti+"'><img width='100' src='"+v.bukti+"'></a></td></tr>");
                                no++;
                            });
                        }else{
                            $("#tbody").html("");
                            $("#tbody").append(" <tr><td colspan='4'> <p class='text-center'>Data tidak tersedia</p><td> </tr>");
                        }

                    }

                })
                $("#staticBackdrop").modal("show");

            })
        });
        </script>
  </head>
  <body> 
      <h1 class="text-center mt-3 mb-3">Tabungan Qurban IKRAR</h1>
 
      <hr>
      <div class="container">
        <div class="row">
            <div class="col-6"><strong>Mulai</strong> </div>
            <div class="col-6">: 01 Agustus 2021</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Target Selesai</strong> </div>
            <div class="col-6">: 31 Juli 2021</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Target Tabungan</strong> </div>
            <div class="col-6">: Rp. 3.500.000 / orang</div>
        </div>
        
        <div class="row">
            <div class="col-6"><strong>Total Target</strong> </div>
            <div class="col-6">: <?php echo "Rp. ".number_format(mysqli_num_rows($data)*3500000) ?></div>
        </div>

  

        <hr>

<div class=" mx-auto" style="width:100%">
<div class="row">
<div class="col-12 text-center mb-3">
    Transfer ke Rekening : 
    </div>

</div>
<div class="row">
    
    <div class="col-6 text-center">
    <p><img src="https://images.bisnis-cdn.com/posts/2021/02/01/1350506/logo-bank-syariah-indonesia-1.jpg" width="100" height="50" /></p>
    <p class="text-center">Muhammad Ramdhani Salam </p>
    <p class="text-center">7135345207<input type="text" value="7135345207" id="input-bsi" style="position:fixed;right:-100%" /> <i class="far fa-copy copy-bsi"></i></p>
    
</div>
<div class="col-6 text-center">
    <p><img src="https://upload.wikimedia.org/wikipedia/id/thumb/5/55/BNI_logo.svg/1200px-BNI_logo.svg.png" width="100" height="50" /></p>
    <p class="text-center">Muhammad Ramdhani Salam </p>
    <p class="text-center">0265706252 <input type="text" value="0265706252" id="input-bni" style="position:fixed;left:-100%" /> <i class="far fa-copy copy-bni"></i></p>
    </div>
</div>

<div class="row">
<div class="col-12 text-center mb-3">
<a href="https://wa.me/6281221760956" class="btn btn-primary w-100 " style="background:green;border:1px solid white"><i class="fab fa-whatsapp"></i> Kirim Bukti Transfer</a>   </div>
</div>


<div class="row">
<div class="col-12 text-center mb-3">
<a href="#" class="btn btn-primary w-100 btn-input-tabungan " style="background:green;border:1px solid white"><i class="fa fa-plus"></i> Input Tabungan</a>   </div>
</div>

</div>
                
          
<hr>
        <table class="table" id="datatable">
            <thead style="font-weight:bold">
                <tr >
                    <td>No</td>
                    <td>Nama </td>
                    <td>Total Tabungan</td>
                    <td>Pencapaian</td>
                </tr>
            </thead>
            <tbody>
                <?php 
                $pencapaian = 3500000;
                if (mysqli_num_rows($data) > 0) {
                    $no = 0;    
                    $grand = 0;
                    while($row = mysqli_fetch_assoc($data)) { 
                    $no++;    
                    ?> 
                    <tr>
                        <td style="width:5%"><?php echo $no ?></td> 
                        <td><?php echo $row['nama_lengkap'] ?></td> 
                        <td style="text-align:right"><?php echo number_format($row['total']) ?>
                        <a data-id-user="<?php echo $row['id_user'] ?>" class="btn-detail btn-sm" style="background:green;color:white;text-decoration:none">
                            <i class="fa fa-book"></i>
                        <a>
                    </td> 
                        <td>
                            <?php 
                            $percentage = $row['total'] / $pencapaian;
                            $percentage = $percentage * 100;
                            $percentage = round($percentage);
                            $grand+=$row['total'];

                            ?>
                            <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage ?>%;" aria-valuenow="<?php echo $percentage ?>" aria-valuemin="0" aria-valuemax="100"> <?php echo $percentage ?>%</div>
                            </div>
                        </td>
                       
                    </tr>
                    <?php 
                    }
                }
                ?>

            </tbody>
        </table>

        <table>
                            <tr>
                    <td >Total Tabungan</td>
                    <td></td>
                    <td><?php echo number_format($grand); ?></td>
                    <td></td>
                </tr>
            </table>
    </div>


    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Rincian Tabungan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="detail table" id="tabungan">
            <thead>
                <tr>
                    <td>No</td>
                    <td>Waktu Tabung</td>
                    <td>Total</td>
                    <td>Bukti</td>
                </tr>
            </thead>
            <tbody id="tbody">
               
             </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
        </div>
    </div>
    </div>


    
    <!-- Modal -->
    <div class="modal fade" id="staticBackdropInput" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Input Tabungan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="" method="POST"  enctype="multipart/form-data" id="form-tabung">
                <div class="mb-3">
                <label for="exampleFormControlInput1" class="form-label" name="id_user">Pilih Penabung</label>
                <select class="form-control" name="id_user" required>
                    <?php 
                    $sql = "select id,nama_lengkap from user "; 
                    $data = mysqli_query($conn, $sql);
                       while($row = mysqli_fetch_assoc($data)) { 
                        echo "<option value='$row[id]'>$row[nama_lengkap]</option>";
                        }
                    ?> 

                </select>
                </div>
                
                <div class="mb-3">
                <label for="exampleFormControlTextarea1" class="form-label">Nominal Tabungan</label>
                <input type="number" class="form-control" name="total" placeholder="Rp. 0" required>
                </div>

                 <div class="mb-3">
                <label for="exampleFormControlTextarea1" class="form-label">Upload Gambar</label>
                <input type="file" class="form-control" name="gambar" placeholder="Rp. 0" required>
                </div>
     
    </div>
    <div class="modal-footer">
            <input type="submit" value="Simpan" class="btn btn-primary" name="simpan" id="simpan">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
        </div>
    </div>
       </form>
    </div>



  </body>
</html>