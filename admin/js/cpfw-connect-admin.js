/**
 * All of the code for your admin-facing JavaScript source
 * should reside in this file.
 */


function childOf(c, p) {
    while ((c = c.parentNode) && c !== p);
    return !!c;
}

/* First helper functions */
function disableBtn(selector) {
    if (!selector) return;
    // if string select the element, if not just use the selector itself
    const button = typeof selector == "string" ? document.querySelector(selector) : selector;
    // some styling and disable button while sending...
    button.disabled = true;
    button.style.pointerEvents = "none";
    button.style.backgroundColor = "#333";
}

function resetBtn(selector, delay = 0) {
    if (!selector) return;
    // if string select the element, if not just use the selector itself
    const button = typeof selector == "string" ? document.querySelector(selector) : selector;

    setTimeout(() => {
        // some styling and disable button while sending...
        button.disabled = false;
        button.style.pointerEvents = "";
        button.style.backgroundColor = "";
    }, delay);
}

function addMessageToHtml(selector, message, newClass, oldClass , newTag) {
    let element = typeof selector == "string" ? document.querySelector(selector) : selector;
    if (element) {
        const spanElement = document.createElement('span');
        spanElement.textContent = message ?? "";
        element.innerHTML = "";
        element.appendChild(spanElement);
        if (newTag) {
            spanElement.append(newTag);
        }
        element.classList.add(newClass);
        element.classList.remove(oldClass);
        
    }
}

function createAnchorElement(linkRef = '', linkText = '') {

    const anchorElement = document.createElement('a');

    anchorElement.href = (new URL(linkRef)).href;
    anchorElement.textContent = ' ' + linkText;
    anchorElement.target = '_blank';

    return anchorElement;
}

/* Send Ajax request to send order to olivery */
const sendOrderToOc = async (orderId) => {
    let data = new FormData(); // create an Object and take your data
    data.append("action", "cpfw_send_order_to_oc");
    data.append("nonce", olivery_ajax_object.nonce);
    data.append("order_id", orderId);

    const response = await fetch(olivery_ajax_object.ajax_url, {
        method: "POST",
        headers: {
            Accept: "application/json",
        },
        body: data,
    });

    return response;
};

// mapping the error message 
const replaceErrorPhrases = (messages) => {
    // return if the response message not array 
    if (!Array.isArray(messages)) return messages;
    return messages.map(message => {
        // Check if the message contains the phrases to replace
        if (message.includes('sub_area') || message.includes('area') || message.includes('sub area')) {
        return message.replace(/sub_area/g, 'address 1')
                        .replace(/area/g, 'city')
                        .replace(/sub area/g,'address 1');
        }
        // Return the message unchanged if no phrases are found
        return message;
    });
};

/* Add event listener to submit button to send in Bulk   */
document.addEventListener("submit", async function (e) {
    if (e.target.id != "wc-orders-filter") return;

    const val = document.querySelector("#wc-orders-filter select[name=action]");
    var auto_send_status = olivery_ajax_object.texts.auto_send_status

    // for auto send status 
    if (!val || val.value.split("mark_")[1] == auto_send_status.split("wc-",2)[1]){
        // for Send to Olivery-Connect
    }else if (!val || val.value != "mark_send-oc") 
        {
            return;
        }
    
    e.preventDefault();

    disableBtn("input#doaction");

    const ids = [];
    document.querySelectorAll("input[name='id[]']:checked").forEach(function (e) {
        ids.push(e.value);
    });

    ids.reverse();

    for (let id of ids) {
        let response = await sendOrderToOc(id);
        let responseJson = await response.json();
        try {

            if (response.ok) {
                if (responseJson.success) {
                    addMessageToHtml(`tr[id="order-${id}"] .olivery_sequence_code `, "Order has been successfully sent to Olivery Connect Plus, Order Sequence ["+responseJson?.data?.message+"]", "alert-success", "status-processing");
                }

                if (!responseJson.success) {

                    if(!responseJson?.data.error?.message) {
                        errorMessage = replaceErrorPhrases(responseJson?.data?.message);
                    }else{
                        errorMessage = replaceErrorPhrases(responseJson?.data?.error?.message);
                    }

                    let anchorElement;
                    if (responseJson.data.error_link){

                        anchorElement = createAnchorElement(responseJson.data.error_link.link_ref ?? '',' '+responseJson.data.error_link.link_text);

                        }
                
                    throw {errorMessage: errorMessage ?? "Unkown Error!",anchorElement: anchorElement};
                }
                continue;
            }
            
        } catch (error) {
            addMessageToHtml(`tr[id="order-${id}"] .olivery_sequence_code `, error.errorMessage, "alert-danger", "status-processing", error.anchorElement);
            continue;
        }
    }
    resetBtn("input#doaction", 500);
});

(function ($) {
    ("use strict");

    /**
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     */

    $(document).on("click", "#submit_to_olivery", async function (e) {
        disableBtn(e.target);
        
        this.innerHTML = e.target.getAttribute("data-sending");
        $(".olivery-connect #modal-urls .alert").hide();

        // var will be true if order sent successfully
        let orderSucces = false;

        try {
            const response = await sendOrderToOc(e.target.getAttribute("data-id"));

            if (!response.ok) throw {text: await response.text()};

            if (response.ok) {
                const responseJson = await response.json();
                if (responseJson.success) {
                    $(".olivery-connect #modal-urls .alert").show().addClass("alert-success").text(responseJson.data.message);
                    // remove send button
                    $(".send-to-olivery-status.button-primary").remove();
                    // show secondary button and put the sequance code inside
                    $(".send-to-olivery-status.button-secondary").show();
                    $(".send-to-olivery-status.button-secondary b").text(responseJson.data.sequence ?? "");
                    // indicator that order sent successfully
                    orderSucces = true;
                    location.reload();
                }

                if (!responseJson.success) {
                    let errorMsg = "";
                    let anchorElement;
                    if (responseJson.data.error.message) errorMsg = replaceErrorPhrases(responseJson.data.error.message);
                    else
                    if (responseJson.data.error) errorMsg = replaceErrorPhrases(responseJson.data.error);
                    
                    if (responseJson.data.error_link){
                        anchorElement = createAnchorElement(responseJson.data.error_link.link_ref ?? '',' '+responseJson.data.error_link.link_text);
                    }
                    throw {text: errorMsg || "Unexpected error", anchorElement: anchorElement};
                }
            }
        } catch (err) {

            // catch any errors and show it in alert
            $(".olivery-connect #modal-urls .alert").show().addClass("alert-danger").text(err.text);

            if (err.anchorElement){
                $('.olivery-connect #modal-urls .alert').append(err.anchorElement) 
            }
        }
        

        // hide the modal after seconds
        setTimeout(() => {
            if (orderSucces) {
                // if order success hide then remove it
                document.querySelector(".olivery-connect #modal-urls").remove();
                return true;
            }
            // if not success enable the send button
            resetBtn(e.target);
            this.innerHTML = e.target.getAttribute("data-text");
        }, 5000);
    });

    /* ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
})(jQuery);
