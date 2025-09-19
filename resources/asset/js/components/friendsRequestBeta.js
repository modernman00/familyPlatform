<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook-Style Friend Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1877f2;
            --secondary-color: #42b72a;
            --bg-color: #f0f2f5;
            --card-bg: #ffffff;
            --text-color: #1c1e21;
            --border-color: #dddfe2;
            --hover-color: #f2f2f2;
            --shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            padding: 20px;
        }

        .friend-requests-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 0;
        }

        .friend-requests-header {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.25rem;
            font-weight: 600;
        }

        .friend-requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
            padding: 16px;
        }

        .friend-request-card {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s;
        }

        .friend-request-card:hover {
            transform: translateY(-2px);
        }

        .friend-request-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .friend-request-content {
            padding: 12px;
        }

        .friend-request-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .friend-request-mutual {
            color: #65676B;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        .friend-request-actions {
            display: flex;
            gap: 8px;
        }

        .btn-confirm {
            background-color: var(--primary-color);
            color: white;
            flex: 1;
            border: none;
            border-radius: 6px;
            padding: 8px 0;
            font-weight: 600;
        }

        .btn-confirm:hover {
            background-color: #166fe5;
        }

        .btn-delete {
            background-color: #e4e6eb;
            color: var(--text-color);
            flex: 1;
            border: none;
            border-radius: 6px;
            padding: 8px 0;
            font-weight: 600;
        }

        .btn-delete:hover {
            background-color: #d8dadf;
        }

        @media (max-width: 768px) {
            .friend-requests-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="friend-requests-container">
        <div class="friend-requests-header">
            Friend Requests
        </div>
        <div class="friend-requests-grid">
            <!-- Friend Request 1 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Abdulazeez Abimbola" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Abdulazeez Abimbola</div>
                    <div class="friend-request-mutual">3 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 2 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/women/2.jpg" alt="Joy Olaogun" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Joy Olaogun</div>
                    <div class="friend-request-mutual">6 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 3 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/3.jpg" alt="Okibode Babatunde" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Okibode Babatunde</div>
                    <div class="friend-request-mutual">7 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 4 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/4.jpg" alt="Lee King" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Lee King</div>
                    <div class="friend-request-mutual">3 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 5 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/5.jpg" alt="Olaogun Monsuru Adisa" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Olaogun Monsuru Adisa</div>
                    <div class="friend-request-mutual">8 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 6 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/6.jpg" alt="Babalola Oluwasuyi" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Babalola Oluwasuyi</div>
                    <div class="friend-request-mutual">8 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 7 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/7.jpg" alt="Danny Greenfield" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Danny Greenfield</div>
                    <div class="friend-request-mutual">14 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 8 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/women/8.jpg" alt="Carla Silva" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Carla Silva</div>
                    <div class="friend-request-mutual">3 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 9 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/9.jpg" alt="Nnaemeka Nwodo" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Nnaemeka Nwodo</div>
                    <div class="friend-request-mutual">5 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 10 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/men/10.jpg" alt="Adeusi Adeyinka" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Adeusi Adeyinka</div>
                    <div class="friend-request-mutual">3 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 11 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/women/11.jpg" alt="Sarah Hughes" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Sarah Hughes</div>
                    <div class="friend-request-mutual">8 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Friend Request 12 -->
            <div class="friend-request-card">
                <img src="https://randomuser.me/api/portraits/women/12.jpg" alt="Soremekun Kikelomo" class="friend-request-image">
                <div class="friend-request-content">
                    <div class="friend-request-name">Soremekun Kikelomo</div>
                    <div class="friend-request-mutual">2 mutual friends</div>
                    <div class="friend-request-actions">
                        <button class="btn-confirm">Confirm</button>
                        <button class="btn-delete">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add functionality to the buttons
        document.querySelectorAll('.btn-confirm').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.friend-request-card');
                const name = card.querySelector('.friend-request-name').textContent;
                alert(`You are now friends with ${name}`);
                card.style.opacity = '0.5';
                this.textContent = 'Confirmed';
                this.disabled = true;
                card.querySelector('.btn-delete').textContent = 'Remove';
            });
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.friend-request-card');
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                }, 300);
            });
        });
    </script>
</body>
</html>