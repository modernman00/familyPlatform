<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook-Style Friend Requests</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #a31621;
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
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
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
            background: linear-gradient(135deg, var(--primary-color, #1877f2) 0%, #a31621 100%);
            color: white;
            flex: 1;
            border: none;
            border-radius: 10px;
            padding: 8px 12px;
            min-height: 42px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 6px rgba(24, 119, 242, 0.2);
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-confirm:hover {
            background: #a31621;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
        }

        .btn-confirm:active {
            transform: scale(0.98);
        }

        .btn-delete {
            background-color: #f1f5f9;
            color: #475569;
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 12px;
            min-height: 42px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-delete:hover {
            background-color: #e2e8f0;
            color: #1e293b;
            transform: translateY(-1px);
        }

        .btn-delete:active {
            transform: scale(0.98);
        }

        @media (max-width: 768px) {
            .friend-requests-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                Swal.fire('Success', `You are now friends with ${name}`, 'success');
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