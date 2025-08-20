document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const backdrop = document.getElementById("sidebarBackdrop");
    const body = document.body;

    // Hàm để đóng sidebar
    const closeSidebar = () => {
        sidebar.classList.remove("show");
        backdrop.classList.remove("show");
        body.classList.remove("sidebar-open");
    };

    // Hàm để mở sidebar
    const openSidebar = () => {
        sidebar.classList.add("show");
        backdrop.classList.add("show");
        body.classList.add("sidebar-open");
    };
    
    // Xử lý khi nhấn nút toggle
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            if (sidebar.classList.contains("show")) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    // Xử lý khi nhấn vào lớp nền mờ
    if (backdrop) {
        backdrop.addEventListener("click", function () {
            closeSidebar();
        });
    }
});