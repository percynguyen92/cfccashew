Just a simple tool to organize my work in Nigeria

Cấu trúc thư mục

resources/views/
├── components/
│ ├── layout/
│ │ ├── app.blade.php # Layout chính
│ │ ├── header.blade.php # Header với title bar
│ │ └── navigation.blade.php # Navigation breadcrumb
│ ├── ui/
│ │ ├── button.blade.php # Button component
│ │ ├── input.blade.php # Input field component
│ │ ├── table.blade.php # Table wrapper
│ │ ├── pagination.blade.php # Pagination component
│ │ └── modal.blade.php # Modal base component
│ ├── bills/
│ │ ├── bill-list.blade.php # Danh sách bills
│ │ ├── bill-item.blade.php # Item trong danh sách
│ │ ├── bill-modal-form.blade.php # Modal thêm/sửa bill
│ │ ├── bill-detail.blade.php # Chi tiết bill
│ │ └── bill-search.blade.php # Form tìm kiếm
│ ├── containers/
│ │ ├── container-list.blade.php # Danh sách containers
│ │ ├── container-item.blade.php # Item container
│ │ ├── container-modal-form.blade.php # Modal thêm/sửa container
│ │ └── container-cutting-tests.blade.php # Bảng con cutting tests
│ ├── cutting-tests/
│ │ ├── cutting-test-list.blade.php # Danh sách cutting tests
│ │ ├── cutting-test-item.blade.php # Item cutting test
│ │ └── cutting-test-modal-form.blade.php # Modal thêm/sửa cutting test
│ └── shared/
│ ├── confirm-modal.blade.php # Modal xác nhận xóa
│ ├── loading-spinner.blade.php # Loading indicator
│ └── alert.blade.php # Alert messages
├── pages/
│ ├── bills/
│ │ ├── index.blade.php # Trang chính danh sách bills
│ │ └── show.blade.php # Trang chi tiết bill
│ └── layouts/
│ └── master.blade.php # Layout master
