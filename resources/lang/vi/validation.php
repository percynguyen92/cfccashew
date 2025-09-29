<?php

return [
    'custom' => [
        'bill_number' => [
            'required' => 'Số vận đơn là bắt buộc.',
            'max' => 'Số vận đơn không được vượt quá 20 ký tự.',
        ],
        'seller' => [
            'required' => 'Tên người bán là bắt buộc.',
            'max' => 'Tên người bán không được vượt quá 255 ký tự.',
        ],
        'buyer' => [
            'required' => 'Tên người mua là bắt buộc.',
            'max' => 'Tên người mua không được vượt quá 255 ký tự.',
        ],
        'bill_id' => [
            'required' => 'Vui lòng chọn vận đơn hợp lệ.',
            'integer' => 'Vui lòng chọn vận đơn hợp lệ.',
            'exists' => 'Vận đơn đã chọn không tồn tại.',
        ],
        'container_id' => [
            'exists' => 'Container đã chọn không tồn tại.',
            'final_sample_forbidden' => 'Phiếu mẫu cuối không được gắn với container.',
            'container_required' => 'Phiếu kiểm tra container phải gắn với container.',
        ],
        'container_number' => [
            'size' => 'Số container phải đúng 11 ký tự.',
            'regex' => 'Số container phải đúng định dạng ISO (4 chữ cái + 7 chữ số).',
        ],
        'w_jute_bag' => [
            'max' => 'Khối lượng bao không được vượt quá 99.99 kg.',
        ],
        'weights' => [
            'min' => 'Khối lượng không được âm.',
        ],
        'type' => [
            'required' => 'Vui lòng chọn loại kiểm tra hợp lệ.',
            'integer' => 'Vui lòng chọn loại kiểm tra hợp lệ.',
            'in' => 'Vui lòng chọn loại kiểm tra hợp lệ.',
        ],
        'moisture' => [
            'min' => 'Độ ẩm không được âm.',
            'max' => 'Độ ẩm không được vượt quá 100%.',
        ],
        'sample_weight' => [
            'required' => 'Khối lượng mẫu phải từ 1 đến 65.535 gram.',
            'integer' => 'Khối lượng mẫu phải là số nguyên.',
            'min' => 'Khối lượng mẫu phải từ 1 đến 65.535 gram.',
            'max' => 'Khối lượng mẫu phải từ 1 đến 65.535 gram.',
        ],
        'outturn_rate' => [
            'max' => 'Tỷ lệ outturn không được vượt quá 60 lbs/80kg.',
        ],
    ],
];
