$(function(){
    $.ajax({
        url: "https://api.paystack.co/bank",
        type: "GET",
        // data:  data,
        contentType: false,
        cache: false,
        processData:false,
        beforeSend : function(xhr){
            xhr.setRequestHeader ("Authorization", "Bearer sk_live_0a8f380e5208f5723a7383c5c7de54e81ca9ee4c");//live paystack key removed
        },
        success: function(data) {
            // console.log(data);
            // console.log(data.message);

            // var data_ = JSON.parse(jQuery.trim(data));
            // console.log("response", data_);

            var datax = data.data;
            var options = '<option value="">-- select bank --</option>';

            for (let i = 0; i < datax.length; ++i) {
                // do something with `substr[i]`
                options += '<option value="'+datax[i].code+'">'+datax[i].name+'</option>'
            }

            $("#banks").html(options);

        },
        error: function(e) {
            console.log(e);
        }          
    });


    $(document).on('change','#banks', function(){
        $(".result").html("");

        if ($(this).val() == ""){
            $(this).closest('div').addClass("error-border");
        } else{
            $(this).closest('div').removeClass("error-border");
        }
    });


    $(document).on('click','.close__', function(){
        $(".overlay").fadeOut();
    });


    $(document).on('click','.caw', function(){
        $(".overlay").fadeIn();
    });

    $(document).on('keyup','#acc', function(){
        var acc = $(this).val();
        $("#success_").fadeOut();
        $("#error_").fadeOut();

        $(this).closest('div').removeClass("error-border");

        if (acc == ""){
            $(this).closest('div').addClass("error-border");
        }

        if (acc.length == 10){
            var bank = $("#banks").val();

            if (bank == ""){
                $(".result").html('<section class="text-red">Please select a bank first and re-enter the account number to validate the bank details</section>');
            }else{
                $("#success_").hide();
                $("#error_").hide();

                $(".result").html("Resolving account details..");

                $.ajax({
                    url: "https://api.paystack.co/bank/resolve?account_number=" + acc + "&bank_code=" + bank,
                    type: "GET",
                    // data:  data,
                    contentType: false,
                    cache: false,
                    processData:false,
                    beforeSend : function(xhr){
                        $("#success_").fadeOut();
                        $("#error_").fadeOut();
                        xhr.setRequestHeader ("Authorization", "Bearer sk_live_0a8f380e5208f5723a7383c5c7de54e81ca9ee4c");//live paystack key removed
                    },
                    success: function(data) {
                        // console.log(data);
                        // console.log(data.message);

                        if (data.status){
                            $("#success_").fadeIn();
                            $("#error_").fadeOut();

                            $(".result").html('<section class="tgreen__">'+data.data.account_name+'</section>');
                        } else{
                            $("#error_").fadeIn();
                            $("#success_").fadeOut();

                            $(".result").html('<section class="text-red">'+data.message+'</section>');

                        }
            
                    },
                    error: function(e) {
                        console.log(e);

                        $("#error_").fadeIn();
                        $("#success_").fadeOut();

                        console.log(e.responseJSON.message);
                        $(".result").html('<section class="text-red">'+e.responseJSON.message+'</section>');
                    }          
                });
            }
        } else{
            $(".result").html("");
        }
    });

    $("#send-money").submit(function(event) {
        let isValid = true;

        // Loop through required fields
        $("#send-money input, #send-money select").each(function() {
            if ($(this).val().trim() === "") {
                $(this).closest('div').addClass("error-border");
                isValid = false;
            } else {
                $(this).closest('div').removeClass("error-border");
            }
        });

        // Prevent form submission if fields are empty
        if (!isValid) {
            event.preventDefault();
            // alert("Please fill in all required fields.");
        } else{
            //valid
            event.preventDefault(); // Prevent default form submission
    
            var formData = new FormData(this); // Automatically captures all form inputs
        
            $.ajax({
            url: 'process-withdrawal.php', // Your server endpoint
            type: 'POST',
            data: formData,
            contentType: false,  // Important for file upload
            processData: false,  // Prevent jQuery from automatically transforming the data
            beforeSend: function() {
                // 👇 Before processing begins
                $('#ttm_').prop('disabled', true);
                $('#loader').show();
            },
            success: function(response) {
                console.log('Success:', response);
                var json = response;

                if (json.hasOwnProperty('status') && json.status === 500){
                    $("#rsp").html("<div class='response_ response_50'><strong>TRANSFER ERROR:</strong> Your transfer FAILED, please refer to administrator with this complaint if this persist because it is a SERVER ERROR.</div>");
                } else if (json.hasOwnProperty('status') && json.status === "true"){
                    $(".fhjs__lo").fadeIn();
                    $(".live-payment").fadeOut();

                    $("#dhjs_pso").html("<div class='response_ response_50'><strong>TRANSFER SUCCESS:</strong> Transfer of NGN "+json.data.currency+" "+json.data.amount+" was initiated successfully with reference: "+json.data.reference+".</div>");

                } else{
                    $("#rsp").html("<div class='response_ response_50'><strong>TRANSFER FAILED:</strong> Your transfer FAILED because we are unable to process your payout to recipient's bank account.</div>");
                }

            },
            error: function(xhr, status, error) {
                console.error('Error:', error);

                $("#rsp").html("<div class='response_'>Error: "+error+"</div>");
            },
            complete: function() {
                // 👇 After processing ends (success or error)
                $('#ttm_').prop('disabled', false);
                $('#loader').hide();
            }
            });
        }
    });

});