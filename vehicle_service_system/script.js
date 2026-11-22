document.addEventListener("DOMContentLoaded", function () {
    const bookingForm = document.getElementById("bookingForm");
    if (bookingForm) {
        bookingForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const phone = document.getElementById("phone").value.trim();
            if (!/^\d{10}$/.test(phone)) {
                alert("Please enter a valid 10-digit phone number.");
                return;
            }

            const formData = new FormData(bookingForm);

            fetch("save_booking.php", {
                method: "POST",
                body: formData
            })
            .then(async (res) => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (err) {
                    throw new Error("Invalid server response: " + text);
                }
            })
            .then((data) => {
                if (data.status === "success") {
                    alert("Booking confirmed! Booking ID: #" + data.booking_id + " | Service Fee: Rs. " + data.amount);
                    window.location.href = "payment.html?booking_id=" + data.booking_id + "&amount=" + data.amount;
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch((err) => alert("Server connection error: " + err.message));
        });
    }

    const paymentForm = document.getElementById("paymentForm");
    if (paymentForm) {
        const params = new URLSearchParams(window.location.search);
        const urlBookingId = params.get("booking_id");
        const urlAmount = params.get("amount");

        if (urlBookingId) {
            document.getElementById("booking_id").value = urlBookingId;
        }
        if (urlAmount) {
            document.getElementById("display_amount").innerText = "Rs. " + parseFloat(urlAmount).toLocaleString('en-US', { minimumFractionDigits: 2 });
        }

        const methodSelect = document.getElementById("payment_method");
        const cardFields = document.getElementById("cardFields");

        function toggleCardFields() {
            if (methodSelect.value === "Cash at Center") {
                cardFields.style.display = "none";
            } else {
                cardFields.style.display = "block";
            }
        }
        methodSelect.addEventListener("change", toggleCardFields);
        toggleCardFields();

        paymentForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const bookingId = document.getElementById("booking_id").value.trim();
            const method = methodSelect.value;

            if (!bookingId) {
                alert("Please enter a Booking ID.");
                return;
            }

            if (method !== "Cash at Center") {
                const cardNum = document.getElementById("card_number").value.replace(/\s+/g, '');
                const expiry = document.getElementById("expiry").value.trim();
                const cvv = document.getElementById("cvv").value.trim();

                if (cardNum.length < 12 || !/^\d+$/.test(cardNum)) {
                    alert("Please enter a valid card number.");
                    return;
                }
                if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                    alert("Please enter the expiry date in MM/YY format.");
                    return;
                }
                if (!/^\d{3,4}$/.test(cvv)) {
                    alert("Please enter a valid CVV.");
                    return;
                }
            }

            const formData = new FormData(paymentForm);

            fetch("process_payment.php", {
                method: "POST",
                body: formData
            })
            .then(async (res) => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (err) {
                    throw new Error("Invalid server response: " + text);
                }
            })
            .then((data) => {
                const resultBox = document.getElementById("paymentResult");
                if (data.status === "success") {
                    resultBox.style.display = "block";
                    resultBox.innerHTML = `
                        <h3 style="color:#15803d;">Payment Successful!</h3>
                        <p><strong>Transaction Reference:</strong> ${data.transaction_ref}</p>
                        <p><strong>Paid Amount:</strong> Rs. ${data.amount}</p>
                        <br>
                        <a href="index.html" class="btn btn-sm">Back to Home</a>
                    `;
                    paymentForm.reset();
                } else {
                    alert("Payment Error: " + data.message);
                }
            })
            .catch((err) => alert("Payment processing error: " + err.message));
        });
    }
});
