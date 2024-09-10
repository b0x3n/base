var __confirmAction = function()
    {
        let     __action;


        let     __showConfirm = function(
                    title, question, confirm, reject, action
                )
                {
                    __action = action;

                    $("#confirm-title").html(title);
                    $("#confirm-question").html(question);
                    $("#confirm-confirm").html(confirm);
                    $("#confirm-cancel").html(reject);
                    $("#confirm-action").html(action);

                    $("#overlay").css("display", "block")
                    $("#confirm-modal").css("display", "block");
                
                    __enableMouse();
                };


        var     __enableMouse = function()
                {
                    $("#confirm-close").unbind("click");
                    $("#confirm-cancel").unbind("click");
                    $("#confirm-confirm").unbind("click");
                
                    $("#confirm-close").bind("click", function() {
                        __closeModal();
                    });

                    $("#confirm-cancel").bind("click", function() {
                        __closeModal();
                    });

                    $("#confirm-confirm").bind("click", function() {
                        window.location.href = __action;
                    });
                };


        var     __closeModal = function()
                {
                    $("#confirm-modal").css("display", "none");
                    $("#overlay").css("display", "none");
                };


        return {
            "showConfirm": __showConfirm
        }
    }

