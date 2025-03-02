            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link<?= urlIs('/') ? ' active' : '' ?>" href="/">Dashboard</a>
                </li>
                <?php if(isLoggedIn()) : ?>
                <li class="nav-item">
                    <a class="nav-link<?= urlIs('/projects*') ? ' active' : '' ?>" href="/projects">Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= urlIs('/tasks*') ? ' active' : '' ?>" href="/tasks">Tasks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= urlIs('/departments*') ? ' active' : '' ?>" href="/departments">Departments</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Views
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item<?= urlIs('/dashboard/calendar') ? ' active' : '' ?>" href="/dashboard/calendar">Calendar</a></li>
                        <li><a class="dropdown-item<?= urlIs('/dashboard/gantt') ? ' active' : '' ?>" href="/dashboard/gantt">Gantt Chart</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link<?= urlIs('/pages/about') ? ' active' : '' ?>" href="/pages/about">About</a>
                </li>
            </ul> 