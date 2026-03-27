<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="index.css">
</head>
<body>
    
    <div>
        <div class="text-center p-4 mt-60 mb-40">
            <img src="opay_transparent.png" class="logo__" alt="">
        </div>

        <div class="text-center mb-40">
            <section class="opacity5">You are withdrawing:</section>
            <h1 class="amount"><span>&#8358;</span>2,500</section>
        </div>

        <div class="tabs">
            <input class="input" name="tabs" type="radio" id="tab-1" checked="checked" />
            <label class="label" for="tab-1">Other Banks</label>
            <div class="panel">
                <h1>Send Money to Other Banks</h1>

                <section class="opacity5 mt-30 mb-10">Select Destination Bank</section>
                <select id="mySelect" style="width: 100%;">
                    <option value="ng" data-image="https://flagcdn.com/w40/ng.png">Nigeria</option>
                    <option value="us" data-image="https://flagcdn.com/w40/us.png">USA</option>
                </select>


                <div class="dark_box mt-30">
                    <section class="opacity5 mb-10">Recipient Account</section>
                    <input type="text" placeholder="Enter Bank Account No." class="formc">
                </div>
                
            </div>
            <input class="input" name="tabs" type="radio" id="tab-2" />
            <label class="label" for="tab-2">Opay Wallet</label>
            <div class="panel">
                <h1>Send Money to Opay Wallet</h1>

                <div class="dark_box mt-30">
                    <section class="opacity5 mb-10">Recipient Account</section>
                    <input type="text" placeholder="Phone No./Opay Account No./Name" class="formc">
                </div>

            </div>
        </div>

        <div class="p-4 text-center">
            <section class="opacity5">
                Wrong amount entered for withdrawal?
            </section>

            <button class="rrd">Cancel Withdrawal</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $('#mySelect').select2({
            width: '100%'
        });

        // $('#mySelect').select2({
        //     templateResult: formatOption,
        //     templateSelection: formatOption
        // });

        // function formatOption(option) {
        //     if (!option.id) return option.text;

        //     var img = $(option.element).data('image');

        //     return $(`
        //         <span>
        //         <img src="${img}" style="width:20px; margin-right:8px;" />
        //         ${option.text}
        //         </span>
        //     `);
        // }

        $('#mySelect').select2({
            minimumResultsForSearch: 0,
            dropdownCssClass: "antd-dropdown",
            selectionCssClass: "antd-selection",
            templateResult: formatOption,
            templateSelection: formatOption
        });

        function formatOption(option) {
            if (!option.id) return option.text;

            const img = $(option.element).data('image');

            return $(`
                <div class="antd-option">
                <img src="${img}" />
                <span>${option.text}</span>
                </div>
            `);
        }
    </script>
</body>
</html>