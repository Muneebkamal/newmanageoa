<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="" class="logo">
                        <h4 class="logo-lg">
                            oamanage
                        </h4>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                @php
                    use App\Models\User;
                    $employees = User::whereNotNull('sync_lead_url')->orderBy('name')->get();
                @endphp
                <div class="dropdown ms-3 header-item">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="employeeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Employees Daily Report
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="employeeDropdown">
                        @foreach($employees as $employee)
                            <li>
                                <a class="dropdown-item" href="{{ route('reports.daily', ['employee' => $employee->id, 'date' => date('Y-m-d')]) }}">
                                    {{ $employee->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/avatar-1.jpg') }}"
                                alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                    {{ Auth::user()->name ?? '' }}
                                </span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome {{ Auth::user()->name ?? '' }}!</h6>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item"
                            onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                            <span class="text-muted fs-16 align-middle me-1">Logout</span>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
