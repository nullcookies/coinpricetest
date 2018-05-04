<?php
	$arrayUsers 	=	getDbUsers();
	echo '<pre>';
	print_r($arrayUsers);
	echo '</pre>';
?>
<div class="bs-example tables_users" data-example-id="simple-table">
	<table class="table">
	 <caption>Thông tin bảng Users</caption>
	 <thead>
	 	<tr>
	 		<th>#</th>
	 		<th>Username</th>
	 		<th>Password</th>
	 		<th>Họ Tên</th>
	 		<th>Facebook</th>
	 		<th>Telegram Id</th>
	 		<th>Email</th>
	 		<th>Quyền hạn</th>
	 		<th>Thao tác</th>
	 	</tr>
	 </thead>
	 <tbody>
	 	<tr>
	 		<?php foreach($arrayUsers as $key => $value) : ?>
	 			<th scope="row"><?php echo $key+1; ?></th>
		 		<td><?php echo $value['username']; ?></td>
		 		<td><?php echo $value['password']; ?></td>
		 		<td><?php echo $value['ho_ten']; ?></td>
		 		<td><?php echo $value['facebook']; ?></td>
		 		<td><?php echo $value['telegram_id']; ?></td>
		 		<td><?php echo $value['email']; ?></td>
		 		<td><?php echo $value['roles']; ?></td>
		 		<td><?php echo $value['roles']; ?></td>
	 		<?php endforeach; ?>
	 	</tr>
	 </tbody> 
	</table>
</div>