The flow is always:

    a) prizma hits you with PREPARE_WITHDRAW status
    b) you respond with URL/FORM and PENDING status
    c) you track what the customer is doing, once he/she finishes up, you send NEW_RESERVATION status to prizma (via notify)
    d) after that, you wait for prizma to hit you on /withdraw route with NEW status
    e) your respond to it with PENDING and then send request to PROVIDER side and wait their response
    f) if they respond to you with success, you send notify to prizma with CREATE_TRANSFER status