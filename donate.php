<?php
$pageMetaTitle = 'Donate to PawBridge Rescue - Change an Animal\'s Life';
$pageMetaDesc = 'Your donation helps rescue, transport, feed, and provide medical care to animals in need. Every dollar saves lives.';
require_once __DIR__ . '/includes/header.php';

if (is_post() && isset($_POST['submit_donation'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token.');
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        $customAmount = (float)($_POST['custom_amount'] ?? 0);
        if ($customAmount > 0) $amount = $customAmount;
        $data = [
            'donor_name' => clean_input($_POST['donor_name'] ?: 'Anonymous'),
            'donor_email' => clean_input($_POST['donor_email'] ?? ''),
            'donor_phone' => clean_input($_POST['donor_phone'] ?? ''),
            'amount' => $amount,
            'currency' => clean_input($_POST['currency'] ?? 'USD'),
            'donation_type' => clean_input($_POST['donation_type'] ?? 'One Time'),
            'payment_method' => clean_input($_POST['payment_method'] ?? 'Card'),
            'message' => clean_input($_POST['message'] ?? ''),
            'anonymous' => isset($_POST['anonymous']) ? 1 : 0,
            'status' => 'Pending',
        ];
        if ($amount <= 0) {
            flash('error', 'Please enter a valid donation amount.');
        } else {
            $insertId = pdo_insert('donations', $data);
            $_SESSION['donation_amount'] = $amount;
            $_SESSION['donation_id'] = $insertId;
            flash('success', 'Thank you for your generous donation of $' . number_format($amount, 2) . '! Your support means the world to the animals.');
            redirect('donate.php#thankyou');
        }
    }
}

$tiers = [
    ['amount' => 25, 'label' => 'Food & Supplies', 'desc' => 'Feeds a rescue pet for 2 weeks', 'featured' => false, 'icon' => 'fa-bowl-food'],
    ['amount' => 50, 'label' => 'Vaccinations', 'desc' => 'Covers core vaccines for one animal', 'featured' => true, 'icon' => 'fa-syringe'],
    ['amount' => 100, 'label' => 'Transport & Rescue', 'desc' => 'Helps transport an animal to safety', 'featured' => false, 'icon' => 'fa-truck-medical'],
    ['amount' => 250, 'label' => 'Medical Care', 'desc' => 'Emergency vet care & spay/neuter', 'featured' => false, 'icon' => 'fa-stethoscope'],
];
?>
<section class="page-hero">
    <span class="ph-eyebrow" style="background:#FEF3C7;color:#92400E;">Every Dollar Counts</span>
    <h1>Donate &amp; <em style="color:var(--pb-accent);font-style:italic;">Save Lives</em></h1>
    <p>Your support helps provide rescue, transport, food, medical care, and safe homes for animals at risk. 100% of donations go directly toward animal care.</p>
</section>

<section class="section" id="thankyou">
    <div class="container-xl">
        <?= display_flash() ?>
        <div class="pb-section-title">
            <span class="eyebrow">Choose Impact</span>
            <h2>How Your <em>Donation Helps</em></h2>
            <p>Select an amount below or enter a custom amount. Every contribution makes a real difference.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-card">
                    <form method="POST" id="donationForm" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <h3 style="margin-bottom:20px;">Select an Amount</h3>
                        <div class="donation-tiers">
                            <?php foreach ($tiers as $t): ?>
                                <div class="tier-card<?= $t['featured'] ? ' featured' : '' ?>" onclick="selectTier(<?= $t['amount'] ?>, this)">
                                    <i class="fas <?= $t['icon'] ?>" style="color:var(--pb-teal);font-size:28px;margin-bottom:10px;"></i>
                                    <div class="tier-amount">$<?= $t['amount'] ?></div>
                                    <div class="tier-label"><?= $t['label'] ?></div>
                                    <small><?= $t['desc'] ?></small>
                                    <input type="radio" name="amount" value="<?= $t['amount'] ?>" class="d-none" <?= $t['featured'] ? 'checked' : '' ?>>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="margin:32px 0;padding:20px;background:var(--pb-soft-bg);border-radius:var(--radius-lg);text-align:center;">
                            <label class="form-label fw-bold d-block" style="margin-bottom:12px;">Or enter a custom amount</label>
                            <div class="input-group" style="max-width:320px;margin:0 auto;">
                                <span class="input-group-text" style="border-radius:50px 0 0 50px;padding:16px 24px;background:white;border-color:#E5E7EB;font-size:1.4rem;font-weight:700;color:var(--pb-primary);">$</span>
                                <input type="number" name="custom_amount" id="customAmount" class="form-control" placeholder="Enter amount" style="border-radius:0 50px 50px 0;padding:16px 24px;font-size:1.2rem;font-weight:600;border-color:#E5E7EB;" min="1" step="1" oninput="clearTier()">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Donation Type</label>
                                <select name="donation_type" class="form-select">
                                    <option value="One Time">One-time Donation</option>
                                    <option value="Monthly">Monthly (Coming Soon)</option>
                                    <option value="Annual">Annual (Coming Soon)</option>
                                    <option value="In Memory">In Memory / In Honor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                </select>
                            </div>
                        </div>

                        <h3 style="margin:28px 0 20px;">Your Information</h3>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span style="color:var(--pb-danger);">*</span></label>
                                <input type="text" name="donor_name" class="form-control" placeholder="Your name or 'Anonymous'" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span style="color:var(--pb-danger);">*</span></label>
                                <input type="email" name="donor_email" class="form-control" placeholder="donor@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone (optional)</label>
                                <input type="tel" name="donor_phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option>Credit / Debit Card</option>
                                    <option>PayPal</option>
                                    <option>Bank Transfer</option>
                                    <option>Check (Mail-in)</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leave a message (optional)</label>
                                <textarea name="message" class="form-control" rows="3" placeholder="What inspired you to donate today? Or dedicate this gift..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="anonymous" id="anon">
                                    <label class="form-check-label" for="anon">Keep my donation anonymous (do not publish my name)</label>
                                </div>
                            </div>
                        </div>

                        <div style="padding:22px;background:var(--pb-mint);border-radius:var(--radius-md);margin-bottom:28px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <span style="font-weight:600;color:var(--pb-navy);">Your Total Donation:</span>
                                <span id="totalDisplay" style="font-family:var(--pb-heading);font-size:2rem;font-weight:900;color:var(--pb-primary);">$50</span>
                            </div>
                            <p style="margin:0;font-size:0.85rem;color:var(--pb-primary);"><i class="fas fa-circle-info"></i> After clicking below, your donation will be recorded in our system. A PawBridge representative will follow up regarding payment processing.</p>
                        </div>

                        <button type="submit" name="submit_donation" class="btn btn-accent btn-lg w-100 justify-content-center">
                            <i class="fas fa-heart"></i> Complete My Donation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--pb-navy);color:white;">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow" style="background:rgba(89,185,176,0.2);color:var(--pb-teal);">See the Difference</span>
            <h2 style="color:white;">Where Your Money <em style="color:var(--pb-accent);font-style:italic;">Actually Goes</em></h2>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-3">
                <div style="text-align:center;">
                    <div style="font-family:var(--pb-heading);font-size:3rem;font-weight:900;color:var(--pb-mint);">78%</div>
                    <div style="color:rgba(255,255,255,0.7);font-weight:600;">Animal Care</div>
                    <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;">Medical, food, shelter, supplies</p>
                </div>
            </div>
            <div class="col-md-3">
                <div style="text-align:center;">
                    <div style="font-family:var(--pb-heading);font-size:3rem;font-weight:900;color:var(--pb-mint);">12%</div>
                    <div style="color:rgba(255,255,255,0.7);font-weight:600;">Rescue &amp; Transport</div>
                    <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;">Vehicles, fuel, emergency rescues</p>
                </div>
            </div>
            <div class="col-md-3">
                <div style="text-align:center;">
                    <div style="font-family:var(--pb-heading);font-size:3rem;font-weight:900;color:var(--pb-mint);">7%</div>
                    <div style="color:rgba(255,255,255,0.7);font-weight:600;">Outreach &amp; Education</div>
                    <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;">Community programs, events</p>
                </div>
            </div>
            <div class="col-md-3">
                <div style="text-align:center;">
                    <div style="font-family:var(--pb-heading);font-size:3rem;font-weight:900;color:var(--pb-mint);">3%</div>
                    <div style="color:rgba(255,255,255,0.7);font-weight:600;">Administration</div>
                    <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;">Insurance, software, essentials</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container-xl">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="eyebrow d-inline-block mb-3 px-4 py-2 rounded-pill" style="background:#FEF3C7;color:#92400E;font-size:0.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Other Ways to Give</span>
                <h2 style="margin-bottom:24px;">More Ways to <em style="color:var(--pb-teal);font-style:italic;">Make an Impact</em></h2>
                <div style="display:flex;flex-direction:column;gap:22px;">
                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <div style="width:52px;height:52px;border-radius:16px;background:var(--pb-mint);display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--pb-primary);flex-shrink:0;"><i class="fas fa-shop"></i></div>
                        <div>
                            <h4 style="margin-bottom:4px;font-size:1.1rem;">Shop with Amazon Smile</h4>
                            <p style="margin:0;color:var(--pb-gray);font-size:0.92rem;">Amazon donates 0.5% of eligible purchases to PawBridge when you shop at smile.amazon.com.</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <div style="width:52px;height:52px;border-radius:16px;background:#DBFEFE;display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--pb-teal);flex-shrink:0;"><i class="fas fa-gift"></i></div>
                        <div>
                            <h4 style="margin-bottom:4px;font-size:1.1rem;">Wishlist &amp; Supply Drives</h4>
                            <p style="margin:0;color:var(--pb-gray);font-size:0.92rem;">Drop off food, blankets, toys, and other essentials. Check our Amazon Wishlist for current needs.</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <div style="width:52px;height:52px;border-radius:16px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--pb-accent);flex-shrink:0;"><i class="fas fa-hand-holding-heart"></i></div>
                        <div>
                            <h4 style="margin-bottom:4px;font-size:1.1rem;">Workplace Giving</h4>
                            <p style="margin:0;color:var(--pb-gray);font-size:0.92rem;">Many employers match charitable donations — double your impact at no extra cost to you!</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:18px;align-items:flex-start;">
                        <div style="width:52px;height:52px;border-radius:16px;background:#DBEAFE;display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--pb-navy);flex-shrink:0;"><i class="fas fa-scroll"></i></div>
                        <div>
                            <h4 style="margin-bottom:4px;font-size:1.1rem;">Planned Giving</h4>
                            <p style="margin:0;color:var(--pb-gray);font-size:0.92rem;">Include PawBridge in your will or estate plans and create a lasting legacy for animals.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=900&q=80" alt="Happy dog after being rescued" class="img-fluid" style="border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);">
            </div>
        </div>
    </div>
</section>

<script>
let selectedAmount = 50;
function selectTier(amt, el) {
    document.getElementById('customAmount').value = '';
    selectedAmount = amt;
    document.querySelectorAll('.tier-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    updateTotal();
}
function clearTier() {
    document.querySelectorAll('.tier-card').forEach(c => { c.classList.remove('selected'); c.querySelector('input').checked = false; });
    updateTotal();
}
function updateTotal() {
    const custom = parseFloat(document.getElementById('customAmount').value) || 0;
    const total = custom > 0 ? custom : selectedAmount;
    document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
}
document.querySelectorAll('.tier-card input:checked').forEach(i => {
    if (i.closest('.tier-card')) i.closest('.tier-card').classList.add('selected');
});
document.getElementById('customAmount').addEventListener('input', updateTotal);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
