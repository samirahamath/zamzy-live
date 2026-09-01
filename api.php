<?php
// ZAMZY Platform API Handler
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$pdo = getDbConnection();

if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP.'
    ]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Support JSON body payload
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);
if (is_array($jsonData) && isset($jsonData['action'])) {
    $action = $jsonData['action'];
    $_POST = array_merge($_POST, $jsonData);
}

switch ($action) {

    // 1. Submit Project Brief / Inquiry
    case 'submit_inquiry':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $preferred_language = trim($_POST['preferred_language'] ?? 'English');
        $budget = trim($_POST['budget'] ?? '₹25k – ₹75k');
        $role = trim($_POST['role'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $tier = trim($_POST['tier'] ?? 'Tier 02 — Custom App & Web');
        $launch_window = trim($_POST['launch_window'] ?? '');
        $project_type = trim($_POST['project_type'] ?? 'Custom SaaS / Web Platform');
        $requirements = trim($_POST['requirements'] ?? '');
        $reference_url = trim($_POST['reference_url'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($requirements)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields (Name, Email, WhatsApp Phone, Requirements).'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_inquiries` 
                (`name`, `email`, `phone`, `preferred_language`, `budget`, `role`, `company`, `tier`, `launch_window`, `project_type`, `requirements`, `reference_url`, `status`) 
                VALUES (:name, :email, :phone, :preferred_language, :budget, :role, :company, :tier, :launch_window, :project_type, :requirements, :reference_url, 'new')");
            
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':preferred_language' => $preferred_language,
                ':budget' => $budget,
                ':role' => $role,
                ':company' => $company,
                ':tier' => $tier,
                ':launch_window' => $launch_window,
                ':project_type' => $project_type,
                ':requirements' => $requirements,
                ':reference_url' => $reference_url
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Your project brief has been successfully logged! A technical architect from ZAMZY will contact you via WhatsApp in ' . htmlspecialchars($preferred_language) . ' within 48 hours.',
                'inquiry_id' => $pdo->lastInsertId()
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error saving brief: ' . $e->getMessage()
            ]);
        }
        break;

    // 2. Submit SaaS Product Demo Request
    case 'submit_demo':
        $product_name = trim($_POST['product_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($product_name) || empty($phone) || empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide product name, phone, and email.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_demo_requests` (`product_name`, `phone`, `email`, `status`) VALUES (:product_name, :phone, :email, 'pending')");
            $stmt->execute([
                ':product_name' => $product_name,
                ':phone' => $phone,
                ':email' => $email
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Demo request registered! Sandbox credentials for ' . htmlspecialchars($product_name) . ' dispatched to ' . htmlspecialchars($phone) . '.'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error saving demo request: ' . $e->getMessage()
            ]);
        }
        break;

    // 3. Submit Client Review / Testimonial
    case 'submit_review':
        $client_name = trim($_POST['client_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $role = trim($_POST['role'] ?? 'Founder / Client');
        $location = trim($_POST['location'] ?? 'Chennai');
        $rating = intval($_POST['rating'] ?? 5);
        $review_text = trim($_POST['review_text'] ?? '');
        $project_type = trim($_POST['project_type'] ?? 'Custom Software & SaaS');

        if (empty($client_name) || empty($company_name) || empty($review_text)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide your name, company, and review feedback.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_testimonials` 
                (`client_name`, `company_name`, `role`, `location`, `rating`, `review_text`, `project_type`, `is_featured`, `is_published`, `is_approved`) 
                VALUES (:client_name, :company_name, :role, :location, :rating, :review_text, :project_type, 1, 1, 1)");
            
            $stmt->execute([
                ':client_name' => $client_name,
                ':company_name' => $company_name,
                ':role' => $role,
                ':location' => $location,
                ':rating' => $rating,
                ':review_text' => $review_text,
                ':project_type' => $project_type
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your testimonial! Your review is now published on the ZAMZY verified showcase.'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error saving testimonial: ' . $e->getMessage()
            ]);
        }
        break;

    // 4. Submit Freelance / Developer Guild / Career Application
    case 'submit_application':
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location_college = trim($_POST['location_college'] ?? '');
        $primary_skills = trim($_POST['primary_skills'] ?? '');
        $experience_level = trim($_POST['experience_level'] ?? 'College Student / Fresher');
        $availability_hours = trim($_POST['availability_hours'] ?? '15-20 hrs/week');
        $expected_payout = trim($_POST['expected_payout'] ?? 'Project Commission');
        $portfolio_url = trim($_POST['portfolio_url'] ?? '');
        $past_work_notes = trim($_POST['past_work_notes'] ?? '');
        $job_id = !empty($_POST['job_id']) ? intval($_POST['job_id']) : null;

        if (empty($full_name) || empty($phone) || empty($email) || empty($primary_skills) || empty($location_college)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in Name, WhatsApp Phone, Email, Location/College, and Skills.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_careers_applications` 
                (`job_id`, `full_name`, `email`, `phone`, `location_college`, `primary_skills`, `experience_level`, `availability_hours`, `expected_payout`, `portfolio_url`, `past_work_notes`, `status`) 
                VALUES (:job_id, :full_name, :email, :phone, :location_college, :primary_skills, :experience_level, :availability_hours, :expected_payout, :portfolio_url, :past_work_notes, 'new')");
            
            $stmt->execute([
                ':job_id' => $job_id,
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':location_college' => $location_college,
                ':primary_skills' => $primary_skills,
                ':experience_level' => $experience_level,
                ':availability_hours' => $availability_hours,
                ':expected_payout' => $expected_payout,
                ':portfolio_url' => $portfolio_url,
                ':past_work_notes' => $past_work_notes
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Welcome to the ZAMZY Developer Guild! Your profile has been recorded. Our team will WhatsApp you at ' . htmlspecialchars($phone) . ' for matching client project milestones and commission payouts!'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error recording application: ' . $e->getMessage()
            ]);
        }
        break;

    // 5. Fetch Open Jobs
    case 'get_jobs':
        try {
            $stmt = $pdo->query("SELECT * FROM `zamzy_careers_jobs` WHERE `is_active` = 1 ORDER BY `id` DESC");
            $jobs = $stmt->fetchAll();
            echo json_encode([
                'success' => true,
                'data' => $jobs
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching jobs: ' . $e->getMessage()
            ]);
        }
        break;

    // 5. Partial Form Auto-Capture (phone or email typed but not submitted)
    case 'partial_capture':
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($phone) && empty($email)) {
            echo json_encode(['success' => false, 'message' => 'No contact info to save.']);
            exit;
        }

        // Prevent duplicate partials for same phone/email within 30 mins
        $dupCheck = $pdo->prepare("SELECT id FROM `zamzy_inquiries` WHERE (`phone` = :phone OR `email` = :email) AND `status` = 'partial' AND `created_at` > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        $dupCheck->execute([':phone' => $phone, ':email' => $email]);
        if ($dupCheck->fetchColumn()) {
            echo json_encode(['success' => true, 'message' => 'Already tracked.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_inquiries`
                (`name`, `email`, `phone`, `preferred_language`, `budget`, `project_type`, `requirements`, `status`)
                VALUES (:name, :email, :phone, 'English', 'Not specified', 'Not specified', '[PARTIAL — Form abandoned before submission]', 'partial')");
            $stmt->execute([
                ':name' => $name ?: '(Not entered)',
                ':email' => $email ?: '',
                ':phone' => $phone ?: ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Partial capture saved.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // 6. AI Assistant Chatbot (Powered by DeepSeek API with Full Session & Transcript Tracking)
    case 'ai_chat_save_lead':
        $token = trim($_POST['session_token'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM `zamzy_chat_sessions` WHERE `session_token` = :token");
            $stmt->execute([':token' => $token]);
            $sessionId = $stmt->fetchColumn();

            if ($sessionId) {
                $upd = $pdo->prepare("UPDATE `zamzy_chat_sessions` SET `user_name` = :name, `user_phone` = :phone, `user_email` = :email, `status` = 'lead_captured' WHERE `id` = :id");
                $upd->execute([':name' => $name, ':phone' => $phone, ':email' => $email, ':id' => $sessionId]);
            } else {
                $ins = $pdo->prepare("INSERT INTO `zamzy_chat_sessions` (`session_token`, `user_name`, `user_phone`, `user_email`, `status`) VALUES (:token, :name, :phone, :email, 'lead_captured')");
                $ins->execute([':token' => $token, ':name' => $name, ':phone' => $phone, ':email' => $email]);
                $sessionId = $pdo->lastInsertId();
            }

            // Also record in zamzy_inquiries if phone or email is provided so it appears across leads
            if (!empty($phone) || !empty($email)) {
                try {
                    $inqCheck = $pdo->prepare("SELECT id FROM `zamzy_inquiries` WHERE `phone` = :phone OR `email` = :email LIMIT 1");
                    $inqCheck->execute([':phone' => $phone, ':email' => $email]);
                    if (!$inqCheck->fetchColumn()) {
                        $inqStmt = $pdo->prepare("INSERT INTO `zamzy_inquiries` (`name`, `email`, `phone`, `preferred_language`, `budget`, `project_type`, `requirements`, `status`) VALUES (:name, :email, :phone, 'English', 'AI Chat Lead', 'AI Chat Consultation', 'Lead generated via AI Chatbot assistant.', 'new')");
                        $inqStmt->execute([
                            ':name' => $name ?: 'Chat Visitor',
                            ':email' => $email,
                            ':phone' => $phone
                        ]);
                    }
                } catch (Exception $e) {}
            }

            echo json_encode([
                'success' => true,
                'session_token' => $token,
                'session_id' => $sessionId
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'ai_chat':
        $token = trim($_POST['session_token'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $userName = trim($_POST['user_name'] ?? '');
        $userPhone = trim($_POST['user_phone'] ?? '');
        $userEmail = trim($_POST['user_email'] ?? '');
        $historyJson = $_POST['history'] ?? '[]';
        $history = json_decode($historyJson, true);
        if (!is_array($history)) {
            $history = [];
        }

        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
            exit;
        }

        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
        }

        // 1. Get or create session
        $sessionId = null;
        try {
            $stmt = $pdo->prepare("SELECT id, user_name, user_phone, user_email FROM `zamzy_chat_sessions` WHERE `session_token` = :token");
            $stmt->execute([':token' => $token]);
            $session = $stmt->fetch();

            if ($session) {
                $sessionId = $session['id'];
                if (empty($userName) && !empty($session['user_name'])) $userName = $session['user_name'];
                if (empty($userPhone) && !empty($session['user_phone'])) $userPhone = $session['user_phone'];
                if (empty($userEmail) && !empty($session['user_email'])) $userEmail = $session['user_email'];
            } else {
                $ins = $pdo->prepare("INSERT INTO `zamzy_chat_sessions` (`session_token`, `user_name`, `user_phone`, `user_email`, `status`) VALUES (:token, :name, :phone, :email, 'in_progress')");
                $ins->execute([':token' => $token, ':name' => $userName, ':phone' => $userPhone, ':email' => $userEmail]);
                $sessionId = $pdo->lastInsertId();
            }

            // Record user message
            $msgStmt = $pdo->prepare("INSERT INTO `zamzy_chat_messages` (`session_id`, `sender`, `message`) VALUES (:sid, 'user', :msg)");
            $msgStmt->execute([':sid' => $sessionId, ':msg' => $message]);
        } catch (Exception $e) {}

        // 2. Build system prompt & call DeepSeek
        $userContext = "";
        if (!empty($userName)) {
            $userContext = " You are currently speaking with {$userName}. Address them naturally when polite.";
        }

        $systemPrompt = "You are the official AI Technical Consultant for ZAMZY (zamzy.in), a premier digital engineering agency based in Hitech City, Hyderabad, Telangana, India.{$userContext}

Key Information about ZAMZY:
- What we do: Custom SaaS Platform Development, Mobile Apps (iOS & Android with Flutter/React Native/Swift/Kotlin), Enterprise ERP Systems (School, Restaurant & Business ERPs), WhatsApp & IVR Telephony Gateways, High-concurrency Cloud Architecture (AWS, Docker, Kubernetes), and Startup Launchpad Incubation.
- Location: Hitech City, Hyderabad, Telangana, India.
- Email: hello@zamzy.in | WhatsApp available for fast responses.
- Timeline & Delivery: Fast-turnaround MVPs in 7-14 days; Full custom platforms in 4-8 weeks.
- Pricing Tiers:
  * Startup MVP / Prototype: ₹10,000 – ₹25,000
  * Custom Web & Mobile App: ₹25,000 – ₹75,000
  * Enterprise SaaS / ERP: ₹75,000 – ₹2,00,000+
- Careers & Guild: We have a Freelance Developer Guild for college developers and freelancers to work on paid client projects with 10-25% project commissions (accessible via careers.php).
- Tone & Rules: Be concise, direct, helpful, and professional. Keep answers under 3-4 sentences unless deep technical detail is requested. Always prompt users to submit the Project Brief form on the page or contact hello@zamzy.in for formal quotes.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Add recent conversation history for continuity
        if (!empty($history)) {
            $recent = array_slice($history, -8);
            foreach ($recent as $h) {
                if (isset($h['role']) && isset($h['content'])) {
                    $messages[] = [
                        'role' => ($h['role'] === 'user' ? 'user' : 'assistant'),
                        'content' => strval($h['content'])
                    ];
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $apiKey = defined('DEEPSEEK_API_KEY') ? DEEPSEEK_API_KEY : (getenv('DEEPSEEK_API_KEY') ?: 'sk-71bbb2ea1a0e45dcbf2574d6f115aac1');
        $apiUrl = getenv('DEEPSEEK_API_URL') ?: 'https://api.deepseek.com/chat/completions';
        $apiModel = getenv('DEEPSEEK_MODEL') ?: 'deepseek-chat';

        $payload = json_encode([
            'model' => $apiModel,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 450,
            'stream' => false
        ]);

        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            $reply = "I am ZAMZY's AI Technical Assistant. We build scalable SaaS platforms, mobile apps, and ERP systems from Hitech City, Hyderabad. Please submit your requirements in the Project Brief form below or email hello@zamzy.in and our technical lead will connect with you within 48 hours!";
        } else {
            $resData = json_decode($response, true);
            $reply = $resData['choices'][0]['message']['content'] ?? "Thank you for reaching out! Please fill the Project Brief form or contact hello@zamzy.in.";
        }

        // 3. Save bot reply & update session
        if ($sessionId) {
            try {
                $botStmt = $pdo->prepare("INSERT INTO `zamzy_chat_messages` (`session_id`, `sender`, `message`) VALUES (:sid, 'bot', :msg)");
                $botStmt->execute([':sid' => $sessionId, ':msg' => $reply]);

                $updSes = $pdo->prepare("UPDATE `zamzy_chat_sessions` SET `total_messages` = `total_messages` + 2, `last_message` = :lastMsg, `user_name` = COALESCE(NULLIF(:name, ''), `user_name`), `user_phone` = COALESCE(NULLIF(:phone, ''), `user_phone`), `user_email` = COALESCE(NULLIF(:email, ''), `user_email`) WHERE `id` = :sid");
                $updSes->execute([
                    ':lastMsg' => $message,
                    ':name' => $userName,
                    ':phone' => $userPhone,
                    ':email' => $userEmail,
                    ':sid' => $sessionId
                ]);
            } catch (Exception $e) {}
        }

        echo json_encode([
            'success' => true,
            'reply' => $reply,
            'session_token' => $token
        ]);
        break;

    case 'get_chat_transcript':
        $sessionId = intval($_GET['session_id'] ?? $_POST['session_id'] ?? 0);
        if ($sessionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid session ID.']);
            exit;
        }

        try {
            $sesStmt = $pdo->prepare("SELECT * FROM `zamzy_chat_sessions` WHERE `id` = :id");
            $sesStmt->execute([':id' => $sessionId]);
            $session = $sesStmt->fetch();

            $msgStmt = $pdo->prepare("SELECT * FROM `zamzy_chat_messages` WHERE `session_id` = :id ORDER BY `created_at` ASC");
            $msgStmt->execute([':id' => $sessionId]);
            $messages = $msgStmt->fetchAll();

            echo json_encode([
                'success' => true,
                'session' => $session,
                'messages' => $messages
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid API action.'
        ]);
        break;
}
?>
