<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Ensure only admin can access
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Sellers | Green Basket Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <style>
        .seller-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 20px;
        }
        .seller-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .seller-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }
        .seller-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--color-brand-primary, #2eb535);
            margin-bottom: 10px;
        }
        .seller-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .seller-username {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }
        .seller-earned {
            color: #0f9d58;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .seller-rating {
            color: #f7b500;
            margin-bottom: 12px;
            font-size: 1rem;
        }
        .btn-view-profile {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #11c6a1; /* teal-green color similar to your screenshot */
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 16px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(17, 198, 161, 0.3);
            transition: all 0.3s ease;
        }

        .btn-view-profile:hover {
            background: #0fa88b;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(17, 198, 161, 0.4);
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-container">
    <div class="header glass-container">
        <h1>All Sellers</h1>
        <div class="user-info">
            <span>Welcome, Admin</span>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="admin-page-container">
            <h2>Registered Sellers</h2>
            <div class="seller-card-grid">
                <?php
                $query = "
                    SELECT 
                        u.uid, 
                        u.username,
                        p.full_name,
                        p.total_earned,
                        p.avatar,
                        COALESCE(s.avg_rating, 0) AS avg_rating
                    FROM users u
                    LEFT JOIN user_profiles p ON u.uid = p.uid
                    LEFT JOIN seller_ratings s ON u.uid = s.seller_id
                    WHERE u.role = 'seller'
                    ORDER BY s.avg_rating DESC, u.username ASC
                ";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $avatar = !empty($row['avatar']) ? "../account/img/" . htmlspecialchars($row['avatar']) : "../account/img/default_avatar.png";
                        $rating = number_format($row['avg_rating'], 1);
                        $earned = number_format($row['total_earned'], 2);
                        echo "
                        <div class='seller-card'>
                            <img src='{$avatar}' alt='Profile Photo' class='seller-photo'>
                            <div class='seller-name'>" . htmlspecialchars($row['full_name']) . "</div>
                            <div class='seller-username'><i class='fas fa-user'></i> " . htmlspecialchars($row['username']) . "</div>
                            <div class='seller-earned'><i class='fas fa-rupee-sign'></i> {$earned}</div>
                            <div class='seller-rating'><i class='fas fa-star'></i> {$rating}</div>
                            <a href='../account/public_profile.php?uid=" . $row['uid'] . "' class='btn-view-profile'>
                                <i class='fas fa-eye'></i> View Profile
                            </a>
                        </div>";
                    }
                } else {
                    echo "<p>No sellers found.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
