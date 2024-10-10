<div class="container">
    <h1>Coupons</h1>
    <p>Coupons are used to apply discounts to orders.</p>
    <h2>Create Coupon</h2>
    <p>To create a coupon, you need to send a POST request to the /coupons endpoint.</p>
    <h3>Request</h3>
    <p>The request should be a JSON object with the following properties:</p>
    <ul>
        <li>code: The coupon code (string)</li>
        <li>discount: The discount amount (number)</li>
        <li>expiration: The expiration date (string in 'YYYY-MM-DD' format)</li>
    </ul>
</div>