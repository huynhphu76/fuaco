<?php
// Đặt mật khẩu bạn muốn mã hóa vào đây
$mat_khau_can_ma_hoa = '123456'; // <--- THAY MẬT KHẨU CỦA BẠN VÀO ĐÂY

// Tạo chuỗi mã hóa
$chuoi_ma_hoa = password_hash($mat_khau_can_ma_hoa, PASSWORD_DEFAULT);

// Hiển thị kết quả để bạn sao chép
echo "<h1>Mật khẩu cần mã hóa: " . htmlspecialchars($mat_khau_can_ma_hoa) . "</h1>";
echo "<h2>Chuỗi đã mã hóa (để dán vào database):</h2>";
echo '<input type="text" value="' . htmlspecialchars($chuoi_ma_hoa) . '" size="80" onclick="this.select();" readonly>';
echo "<p><i>Nhấn vào ô trên để tự động bôi đen và sao chép.</i></p>";
?>