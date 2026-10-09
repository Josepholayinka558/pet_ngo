<?php
$pageMetaTitle = 'Foster Program - Give an Animal a Safe Place to Heal';
$pageMetaDesc = 'Become a foster home for rescue animals. Give an animal a safe, loving temporary home while they wait for their forever family.';
require_once __DIR__ . '/includes/header.php';

if (is_post() && isset($_POST['submit_foster'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token.');
    } else {
        $data = [
            'full_name' => clean_input($_POST['full_name']),
            'email' => clean_input($_POST['email']),
            'phone' => clean_input($_POST['phone']),
            'address' => clean_input($_POST['address'] ?? ''),
            'city' => clean_input($_POST['city'] ?? ''),
            'country' => clean_input($_POST['country'] ?? ''),
            'housing_type' => clean_input($_POST['housing_type'] ?? ''),
            'housing_details' => clean_input($_POST['housing_details'] ?? ''),
            'previous_experience' => clean_input($_POST['previous_experience'] ?? ''),
            'animal_types' => clean_input(implode(', ', $_POST['animal_types'] ?? [])),
            'availability' => clean_input($_POST['availability'] ?? ''),
            'duration' => clean_input($_POST['duration'] ?? ''),
            'existing_pets' => isset($_POST['existing_pets']) ? 1 : 0,
            'existing_pets_details' => clean_input($_POST['existing_pets_details'] ?? ''),
            'can_foster_count' => (int)($_POST['can_foster_count'] ?? 1),
            'has_children' => isset($_POST['has_children']) ? 1 : 0,
            'transport_available' => isset($_POST['transport_available']) ? 1 : 0,
            'additional_info' => clean_input($_POST['additional_info'] ?? ''),
            'status' => 'New',
        ];
        if (empty($data['full_name']) || empty($data['email']) || empty($data['phone'])) {
            flash('error', 'Please fill in all required fields.');
        } elseif (!validate_email($data['email'])) {
            flash('error', 'Please enter a valid email address.');
        } else {
            pdo_insert('foster_applications', $data);
            flash('success', 'Thank you for your interest in fostering! Your application has been submitted and our foster coordinator will be in touch.');
            redirect('foster.php#applied');
        }
    }
}

$perks = [
    ['icon' => 'fa-syringe', 'title' => 'All Vet Care Covered', 'desc' => 'We cover 100% of medical expenses, vaccinations, and treatments for foster animals.'],
    ['icon' => 'fa-bowl-food', 'title' => 'Food & Supplies Provided', 'desc' => 'Food, bedding, toys, crates, and everything your foster needs is supplied by us.'],
    ['icon' => 'fa-headset', 'title' => '24/7 Support Team', 'desc' => 'Our team is always on call to help with behavior questions, vet concerns, or anything else.'],
    ['icon' => 'fa-heart', 'title' => 'Lifetime Love', 'desc' => 'Foster families often say fostering is one of the most rewarding things they\'ve ever done.'],
];
?>
<section class="page-hero">
    <span class="ph-eyebrow">Make a Real Difference</span>
    <h1>Become a <em style="color:var(--pb-teal);font-style:italic;">Foster Home</em></h1>
    <p>Give an animal a safe, loving temporary place to heal while they wait for their forever family. You save a life by opening your heart — and your home.</p>
</section>

<section class="section">
    <div class="container-xl">
        <div class="row g-5 align-items-center mb-5">
            <div class="col-lg-6">
                <span class="eyebrow d-inline-block mb-3 px-4 py-2 rounded-pill" style="background:var(--pb-mint);color:var(--pb-primary);font-size:0.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Why Foster Matters</span>
                <h2 style="margin-bottom:24px;">Fostering Saves <em style="color:var(--pb-teal);font-style:italic;">More Than One Life</em></h2>
                <p style="font-size:1.05rem;margin-bottom:18px;">Every foster home creates space for another animal to be rescued. When you foster, you're not just helping one animal — you're part of a chain that rescues, heals, and rehomes hundreds of animals each year.</p>
                <p style="margin-bottom:18px;">Foster animals include: moms with newborns, shy rescues learning to trust, senior pets, medical recovery cases, and animals just waiting for their perfect match.</p>
                <ul style="list-style:none;padding:0;margin-top:24px;">
                    <li style="padding:10px 0;"><i class="fas fa-check-circle" style="color:var(--pb-teal);margin-right:10px;"></i> Fosters of every experience level welcome</li>
                    <li style="padding:10px 0;"><i class="fas fa-check-circle" style="color:var(--pb-teal);margin-right:10px;"></i> Short-term and long-term foster opportunities</li>
                    <li style="padding:10px 0;"><i class="fas fa-check-circle" style="color:var(--pb-teal);margin-right:10px;"></i> Full training and ongoing support provided</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=900&q=80" alt="Foster family with rescue dog" class="img-fluid" style="border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);">
            </div>
        </div>

        <div class="pb-section-title mt-5">
            <span class="eyebrow">What We Provide</span>
            <h2>Fostering is <em>Supported</em></h2>
            <p>You provide the love — we provide everything else.</p>
        </div>

        <div class="values-grid">
            <?php foreach ($perks as $p): ?>
            <div class="value-card">
                <div class="value-icon"><i class="fas <?= $p['icon'] ?>"></i></div>
                <h4><?= $p['title'] ?></h4>
                <p><?= $p['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" style="background:var(--pb-soft-bg);" id="applied">
    <div class="container-xl">
        <?= display_flash() ?>
        <div class="pb-section-title">
            <span class="eyebrow">Apply Today</span>
            <h2>Foster <em>Application</em></h2>
            <p>Ready to start your foster journey? Fill out the application below and our foster coordinator will be in touch within 2-4 business days.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="form-card">
                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <h3 style="margin-bottom:20px;">Your Information</h3>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span style="color:var(--pb-danger);">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span style="color:var(--pb-danger);">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span style="color:var(--pb-danger);">*</span></label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" placeholder="United States">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Street Address</label>
                                <input type="text" name="address" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                        </div>

                        <h3 style="margin:28px 0 20px;">Your Home</h3>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Housing Type</label>
                                <select name="housing_type" class="form-select">
                                    <option value="">Select...</option>
                                    <option>House with Yard</option><option>House without Yard</option>
                                    <option>Apartment</option><option>Condo</option>
                                    <option>Townhouse</option><option>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">How many animals can you foster at once?</label>
                                <input type="number" name="can_foster_count" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tell us about your home (yard, flooring, stairs, etc.)</label>
                                <textarea name="housing_details" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="has_children" id="fc">
                                    <label class="form-check-label" for="fc">Children living in the home (under 18)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="existing_pets" id="fep">
                                    <label class="form-check-label" for="fep">I currently have pets</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Details about current pets (type, age, temperament)</label>
                                <textarea name="existing_pets_details" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <h3 style="margin:28px 0 20px;">Preferences & Availability</h3>
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Types of animals you're open to fostering</label>
                                <div class="row" style="padding:8px 4px;">
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Dogs" id="t1"><label class="form-check-label" for="t1">Dogs</label></div></div>
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Cats" id="t2"><label class="form-check-label" for="t2">Cats</label></div></div>
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Rabbits" id="t3"><label class="form-check-label" for="t3">Rabbits</label></div></div>
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Birds" id="t4"><label class="form-check-label" for="t4">Birds</label></div></div>
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Young Puppies/Kittens" id="t5"><label class="form-check-label" for="t5">Puppies/Kittens</label></div></div>
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Seniors" id="t6"><label class="form-check-label" for="t6">Senior Pets</label></div></div>
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Medical Recovery" id="t7"><label class="form-check-label" for="t7">Medical Recovery</label></div></div>
                                    <div class="col-6 col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="animal_types[]" value="Other" id="t8"><label class="form-check-label" for="t8">Other</label></div></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Availability</label>
                                <select name="availability" class="form-select">
                                    <option value="">Select...</option>
                                    <option>Immediate</option><option>Within 2 weeks</option>
                                    <option>Within 1 month</option><option>2+ months</option><option>Flexible</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred foster duration</label>
                                <select name="duration" class="form-select">
                                    <option value="">No preference</option>
                                    <option>Short-term (1-2 weeks)</option><option>Medium (2-6 weeks)</option>
                                    <option>Long-term (1-3 months)</option><option>Until adopted (variable)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="transport_available" id="ft">
                                    <label class="form-check-label" for="ft">I can transport my foster to/from vet appointments</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Previous animal or foster experience</label>
                                <textarea name="previous_experience" class="form-control" rows="3" placeholder="Have you fostered before? Had pets? Volunteered?"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Anything else we should know?</label>
                                <textarea name="additional_info" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <button type="submit" name="submit_foster" class="btn btn-primary btn-lg w-100 justify-content-center">
                            <i class="fas fa-house-heart"></i> Submit Foster Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
