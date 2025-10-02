<?php

return [
    // Flash messages
    'bill_created' => 'Tạo bill thành công.',
    'bill_updated' => 'Cập nhật bill thành công.',
    'bill_deleted' => 'Xóa bill thành công.',
    'container_created' => 'Tạo container thành công.',
    'container_updated' => 'Cập nhật container thành công.',
    'container_deleted' => 'Xóa container thành công.',
    'cutting_test_created' => 'Tạo phiếu kiểm tra cắt thành công.',
    'cutting_test_updated' => 'Cập nhật phiếu kiểm tra cắt thành công.',
    'cutting_test_deleted' => 'Xóa phiếu kiểm tra cắt thành công.',

    // Welcome page
    'welcome' => [
        'pageTitle' => 'Chào mừng đến với CFCCashew',
        'nav' => [
            'dashboard' => 'Bảng điều khiển',
            'login' => 'Đăng nhập',
            'register' => 'Đăng ký',
        ],
        'hero' => [
            'title' => 'Hệ thống kiểm tra CFCCashew',
            'subtitle' => [
                'line1' => 'Quản lý vận đơn, container và kết quả kiểm tra cắt',
                'line2' => 'để kiểm soát chất lượng hạt điều toàn diện.',
            ],
        ],
        'tiles' => [
            'documentation' => [
                'intro' => 'Laravel có tài liệu tuyệt vời bao gồm mọi khía cạnh của framework.',
                'link' => 'Tài liệu',
            ],
            'laracasts' => [
                'link' => 'Laracasts',
            ],
        ],
    ],

    // Common
    'common' => [
        'actions' => [
            'save' => 'Lưu',
            'cancel' => 'Hủy',
            'delete' => 'Xóa',
            'edit' => 'Sửa',
            'create' => 'Tạo',
            'view' => 'Xem',
            'back' => 'Quay lại',
            'close' => 'Đóng',
            'submit' => 'Gửi',
            'reset' => 'Đặt lại',
            'search' => 'Tìm kiếm',
            'filter' => 'Lọc',
            'clear' => 'Xóa',
            'export' => 'Xuất',
            'import' => 'Nhập',
        ],
        'states' => [
            'loading' => 'Đang tải...',
            'saving' => 'Đang lưu...',
            'deleting' => 'Đang xóa...',
            'processing' => 'Đang xử lý...',
        ],
        'messages' => [
            'loading' => 'Đang tải...',
            'noData' => 'Không có dữ liệu',
            'error' => 'Đã xảy ra lỗi',
            'success' => 'Thao tác hoàn thành thành công',
        ],
        'placeholders' => [
            'notAvailable' => 'N/A',
            'selectOption' => 'Chọn một tùy chọn',
            'enterText' => 'Nhập văn bản',
            'search' => 'Tìm kiếm...',
        ],
    ],

    // Dashboard
    'dashboard' => [
        'title' => 'Bảng điều khiển',
        'ranges' => [
            'week' => 'Tuần này',
            'month' => 'Tháng này',
            'year' => 'Năm này',
        ],
        'stats' => [
            'cards' => [
                'totalBills' => [
                    'label' => 'Tổng số Bill',
                    'description' => 'Tổng số bill trong hệ thống',
                ],
                'pendingFinalTests' => [
                    'label' => 'Chờ kiểm tra cuối',
                    'description' => 'Bill đang chờ kiểm tra cắt cuối',
                ],
                'missingFinalSamples' => [
                    'label' => 'Thiếu mẫu cuối',
                    'description' => 'Bill thiếu mẫu cuối bắt buộc',
                ],
                'highMoistureContainers' => [
                    'label' => 'Container độ ẩm cao',
                    'description' => 'Container có độ ẩm > 11%',
                ],
            ],
        ],
    ],

    // Bills
    'bills' => [
        'index' => [
            'title' => 'Vận đơn',
            'description' => 'Quản lý vận đơn và các container liên quan',
            'actions' => [
                'create' => 'Tạo Bill',
            ],
            'search' => [
                'placeholder' => 'Tìm kiếm bill theo số, người bán hoặc người mua...',
                'total' => 'Hiển thị {count} bill',
            ],
            'table' => [
                'headers' => [
                    'billNumber' => 'Số Bill',
                    'seller' => 'Người bán',
                    'buyer' => 'Người mua',
                    'origin' => 'Xuất xứ',
                    'containers' => 'Container',
                    'finalSamples' => 'Mẫu cuối',
                    'averageOutturn' => 'Outturn TB',
                    'created' => 'Tạo lúc',
                    'actions' => 'Thao tác',
                ],
                'finalSamples' => '{count}/3',
                'averageOutturnValue' => '{value} lbs/80kg',
            ],
            'empty' => [
                'title' => 'Không tìm thấy bill',
                'search' => 'Thử điều chỉnh tiêu chí tìm kiếm',
                'default' => 'Bắt đầu bằng cách tạo bill đầu tiên',
            ],
            'pagination' => [
                'summary' => 'Hiển thị {from} đến {to} trong tổng số {total} kết quả',
                'previous' => 'Trước',
                'next' => 'Tiếp',
            ],
            'dialog' => [
                'delete' => [
                    'title' => 'Xóa Bill',
                    'description' => 'Bạn có chắc chắn muốn xóa "{label}"? Hành động này không thể hoàn tác.',
                    'fallback' => 'Bill #{id}',
                ],
            ],
            'sr' => [
                'edit' => 'Sửa bill',
                'delete' => 'Xóa bill',
            ],
        ],
        'form' => [
            'fields' => [
                'billNumber' => [
                    'label' => 'Số Bill',
                ],
                'seller' => [
                    'label' => 'Người bán',
                ],
                'buyer' => [
                    'label' => 'Người mua',
                ],
                'origin' => [
                    'label' => 'Xuất xứ',
                ],
                'samplingRatio' => [
                    'label' => 'Tỷ lệ lấy mẫu',
                ],
                'netOnBl' => [
                    'label' => 'Trọng lượng ròng trên B/L',
                ],
                'quantityOfBagsOnBl' => [
                    'label' => 'Số lượng bao trên B/L',
                ],
                'inspectionStartDate' => [
                    'label' => 'Ngày bắt đầu kiểm tra',
                ],
                'inspectionEndDate' => [
                    'label' => 'Ngày kết thúc kiểm tra',
                ],
                'inspectionLocation' => [
                    'label' => 'Địa điểm kiểm tra',
                ],
                'wDunnageDribag' => [
                    'label' => 'Trọng lượng Dunnage/Dribag',
                ],
                'wJuteBag' => [
                    'label' => 'Trọng lượng bao đay',
                ],
                'note' => [
                    'label' => 'Ghi chú',
                ],
            ],
        ],
    ],

    // Containers
    'containers' => [
        'index' => [
            'title' => 'Container',
            'description' => 'Quản lý thông tin container và dữ liệu trọng lượng',
            'summary' => [
                'total' => 'Tổng: {count} container',
            ],
            'filters' => [
                'truck' => [
                    'placeholder' => 'Tìm kiếm theo xe tải...',
                ],
                'billInfo' => [
                    'label' => 'Thông tin Bill',
                ],
                'dateFrom' => [
                    'label' => 'Từ ngày',
                ],
                'dateTo' => [
                    'label' => 'Đến ngày',
                ],
                'actions' => [
                    'search' => 'Tìm kiếm',
                    'clear' => 'Xóa',
                ],
            ],
            'table' => [
                'outturnValue' => '{value} lbs/80kg',
            ],
            'empty' => 'Không tìm thấy container',
            'pagination' => [
                'summary' => 'Hiển thị {from} đến {to} trong tổng số {total} kết quả',
                'previous' => 'Trước',
                'next' => 'Tiếp',
            ],
            'dialog' => [
                'delete' => [
                    'fallback' => 'Container #{id}',
                ],
            ],
        ],
        'create' => [
            'headTitle' => 'Tạo Container',
            'title' => 'Tạo Container mới',
            'actions' => [
                'back' => 'Quay lại',
            ],
        ],
        'edit' => [
            'headTitle' => 'Sửa Container {identifier}',
            'title' => 'Sửa Container {identifier}',
            'actions' => [
                'back' => 'Quay lại',
            ],
            'bill' => [
                'title' => 'Bill liên kết',
                'number' => 'Số Bill',
                'seller' => 'Người bán',
                'fallback' => 'Bill #{id}',
            ],
        ],
        'show' => [
            'actions' => [
                'addCuttingTest' => 'Thêm phiếu kiểm tra cắt',
            ],
            'cuttingTests' => [
                'title' => 'Phiếu kiểm tra cắt',
                'add' => 'Thêm phiếu',
                'types' => [
                    'finalFirst' => 'Mẫu cuối 1',
                    'finalSecond' => 'Mẫu cuối 2',
                    'finalThird' => 'Mẫu cuối 3',
                    'container' => 'Kiểm tra Container',
                    'generic' => 'Loại {type}',
                ],
                'headers' => [
                    'type' => 'Loại',
                    'moisture' => 'Độ ẩm',
                    'sampleWeight' => 'Trọng lượng mẫu',
                    'nutCount' => 'Số hạt',
                    'defectiveRatio' => 'Tỷ lệ lỗi',
                    'outturn' => 'Outturn',
                    'date' => 'Ngày',
                ],
                'empty' => [
                    'title' => 'Chưa có phiếu kiểm tra cắt',
                    'subtitle' => 'Thêm phiếu kiểm tra cắt đầu tiên cho container này',
                    'action' => 'Thêm phiếu kiểm tra cắt',
                ],
            ],
        ],
        'form' => [
            'fields' => [
                'containerNumber' => [
                    'label' => 'Số Container',
                ],
                'truck' => [
                    'label' => 'Xe tải',
                ],
                'containerCondition' => [
                    'label' => 'Tình trạng Container',
                ],
                'sealCondition' => [
                    'label' => 'Tình trạng niêm phong',
                ],
                'totalWeight' => [
                    'label' => 'Tổng trọng lượng',
                ],
                'truckWeight' => [
                    'label' => 'Trọng lượng xe tải',
                ],
                'containerWeight' => [
                    'label' => 'Trọng lượng container',
                ],
                'dunnageWeight' => [
                    'label' => 'Trọng lượng dunnage',
                ],
            ],
            'calculated' => [
                'heading' => 'Tính toán trọng lượng',
                'gross' => [
                    'label' => 'Trọng lượng thô',
                ],
            ],
        ],
    ],

    // Cutting Tests
    'cuttingTests' => [
        'show' => [
            'pageTitle' => '{type} - Phiếu #{id}',
            'types' => [
                'finalFirst' => 'Mẫu cuối 1',
                'finalSecond' => 'Mẫu cuối 2',
                'finalThird' => 'Mẫu cuối 3',
                'container' => 'Kiểm tra Container',
                'generic' => 'Loại {type}',
            ],
            'meta' => [
                'created' => 'Tạo lúc: {date}',
                'updated' => 'Cập nhật: {date}',
            ],
            'actions' => [
                'back' => 'Quay lại',
                'edit' => 'Sửa',
                'delete' => 'Xóa',
            ],
            'dialog' => [
                'delete' => [
                    'confirm' => 'Bạn có chắc chắn muốn xóa phiếu kiểm tra cắt này?',
                ],
            ],
            'alerts' => [
                'weight' => 'Cảnh báo trọng lượng: chênh lệch {difference}g',
                'defectiveRatio' => 'Cảnh báo tỷ lệ lỗi: chênh lệch {difference}g',
                'goodKernel' => 'Cảnh báo nhân tốt: chênh lệch {difference}g',
            ],
            'sections' => [
                'context' => [
                    'title' => 'Ngữ cảnh',
                    'bill' => [
                        'title' => 'Vận đơn',
                        'seller' => 'Người bán: {value}',
                        'buyer' => 'Người mua: {value}',
                    ],
                    'container' => [
                        'title' => 'Container',
                        'finalSample' => 'Mẫu cuối (không liên kết với container)',
                        'truck' => 'Xe tải: {value}',
                    ],
                ],
                'calculations' => [
                    'formulas' => [
                        'outturn' => [
                            'title' => 'Tỷ lệ Outturn:',
                            'body' => '(Nhân lỗi/2 + Nhân tốt) × 80 ÷ 453.6',
                        ],
                        'defective' => [
                            'title' => 'Nhân lỗi:',
                            'body' => 'Hạt lỗi ÷ 3.3',
                        ],
                    ],
                ],
            ],
        ],
        'index' => [
            'title' => 'Phiếu kiểm tra cắt',
            'description' => 'Quản lý kết quả kiểm tra cắt và dữ liệu kiểm soát chất lượng',
            'actions' => [
                'create' => 'Tạo phiếu kiểm tra',
            ],
            'filters' => [
                'billNumber' => [
                    'label' => 'Số Bill',
                ],
                'testType' => [
                    'label' => 'Loại kiểm tra',
                ],
                'container' => [
                    'label' => 'Container',
                ],
                'dateFrom' => [
                    'label' => 'Từ ngày',
                ],
                'dateTo' => [
                    'label' => 'Đến ngày',
                ],
                'actions' => [
                    'search' => 'Tìm kiếm',
                    'clear' => 'Xóa',
                ],
            ],
            'summary' => [
                'total' => 'Tổng: {count} phiếu kiểm tra',
            ],
            'table' => [
                'billFallback' => 'Bill #{id}',
                'containerFallback' => 'Container #{id}',
                'containerPlaceholder' => 'Mẫu cuối',
                'finalSampleLabel' => 'Mẫu cuối {order}',
                'outturnValue' => '{value} lbs/80kg',
            ],
            'empty' => 'Không tìm thấy phiếu kiểm tra cắt',
            'pagination' => [
                'summary' => 'Hiển thị {from} đến {to} trong tổng số {total} kết quả',
                'previous' => 'Trước',
                'next' => 'Tiếp',
            ],
            'dialog' => [
                'delete' => [
                    'confirm' => 'Bạn có chắc chắn muốn xóa phiếu kiểm tra cắt này?',
                ],
            ],
            'sr' => [
                'view' => 'Xem phiếu kiểm tra cắt',
                'edit' => 'Sửa phiếu kiểm tra cắt',
            ],
        ],
    ],

    // Settings
    'settings' => [
        'profile' => [
            'title' => 'Cài đặt hồ sơ',
            'breadcrumb' => 'Hồ sơ',
            'heading' => [
                'title' => 'Thông tin hồ sơ',
                'description' => 'Cập nhật thông tin hồ sơ tài khoản và địa chỉ email của bạn.',
            ],
            'fields' => [
                'name' => [
                    'label' => 'Tên',
                    'placeholder' => 'Nhập tên của bạn',
                ],
                'email' => [
                    'label' => 'Email',
                    'placeholder' => 'Nhập địa chỉ email của bạn',
                ],
            ],
            'verification' => [
                'unverified' => 'Địa chỉ email của bạn chưa được xác minh.',
                'resend' => 'Nhấp vào đây để gửi lại email xác minh.',
                'sent' => 'Một liên kết xác minh mới đã được gửi đến địa chỉ email của bạn.',
            ],
            'status' => [
                'saved' => 'Đã lưu.',
            ],
        ],
        'twoFactor' => [
            'title' => 'Xác thực hai yếu tố',
            'breadcrumb' => 'Xác thực hai yếu tố',
            'heading' => [
                'title' => 'Xác thực hai yếu tố',
                'description' => 'Thêm bảo mật bổ sung cho tài khoản của bạn bằng xác thực hai yếu tố.',
            ],
            'status' => [
                'enabled' => 'Đã bật',
                'disabled' => 'Đã tắt',
            ],
            'copy' => [
                'enabled' => 'Xác thực hai yếu tố hiện đang được bật cho tài khoản của bạn.',
                'disabled' => 'Xác thực hai yếu tố chưa được bật cho tài khoản của bạn.',
            ],
            'actions' => [
                'enable' => 'Bật',
                'disable' => 'Tắt',
                'continueSetup' => 'Tiếp tục thiết lập',
            ],
        ],
    ],
];
