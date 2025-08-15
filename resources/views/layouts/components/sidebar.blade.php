<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box pt-4 pb-3">
        <a class="logo logo-light">
            <span class="logo-lg">
                <h3 class="text-light">OAMANAGE</h3>
            </span>
        </a>

        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                @can('view_dashboard')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                        <i class="ri-home-4-line"></i> <span data-key="t-widgets">Dashboard</span>
                    </a>
                </li>
                @endcan
                @can('view_my_uploads')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('my-uploads') ? 'active' : '' }}"
                        href="{{ url('/my-uploads') }}">
                        <i class="ri-upload-cloud-2-line"></i> <span data-key="t-widgets">My Uploads</span>
                    </a>
                </li>
                @endcan
                @can('view_leads')
                {{-- <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('leads') ? 'active' : '' }}" href="{{ url('/leads') }}">
                        <i class="ri-filter-2-line"></i> <span data-key="t-widgets">Leads</span>
                    </a>
                </li> --}}
                 <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('leads-new') ? 'active' : '' }}" href="{{ url('/leads-new') }}">
                        <i class="ri-filter-2-line"></i> <span data-key="t-widgets">Leads</span>
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('leads-rejected') ? 'active' : '' }}" href="{{ url('leads-rejected') }}">
                        <i class="ri-filter-2-line"></i> <span data-key="t-widgets">Rejected Leads</span>
                    </a>
                </li>
                @endcan
                @can('view_oa_manage_lists')
                <li class="nav-item d-none">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-file-list-3-line"></i> <span data-key="t-dashboards">OA Manage Lists</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarDashboards">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                Oa Cheddar List
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('dashboard/list') }}" class="nav-link menu-link">Cheddar List #6</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('dashboard/list') }}" class="nav-link menu-link">Feta List #1</a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcan
                @can('view_buy_list')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('buylist') ? 'active' : '' }}"  href="{{ route('buylist.index') }}">
                        <i class="las la-clipboard-list"></i> <span data-key="t-widgets">Buy List</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('aprroved/buylist') ? 'active' : '' }}"  href="{{ route('buylist.index2') }}">
                        <i class="las la-clipboard-list"></i> <span data-key="t-widgets">Aprrove Buy List</span>
                    </a>
                </li>
                @endcan
                @can('view_orders')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('orders') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                        <i class="las la-cart-arrow-down"></i> <span data-key="t-widgets">Orders</span>
                    </a>
                </li>
                @endcan
                @can('view_shipping_new')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('shippingbatches') ? 'active' : '' }}" href="{{ route('shippingbatches.index') }}">
                        <i class="mdi mdi-ship-wheel"></i> <span data-key="t-widgets">Shipping</span>
                    </a>
                </li>
                @endcan
                @can('view_locations')
                <li class="nav-item d-none">
                    <a class="nav-link menu-link {{ Request::is('locations') ? 'active' : '' }}" href="{{ route('locations.index') }}">
                        <i class="bx bx-current-location"></i> <span data-key="t-widgets">Locations</span>
                    </a>
                </li>
                @endcan
                @can('view_users_email')
                <li class="nav-item d-none">
                    <a class="nav-link menu-link {{ Request::is('emails') ? 'active' : '' }}" href="{{ route('emails.index') }}">
                        <i class="mdi mdi-email-multiple-outline"></i> <span data-key="t-widgets">Users Email</span>
                    </a>
                </li>
                @endcan
                @can('view_employees')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('employees') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                        <i class=" ri-user-settings-fill"></i> <span data-key="t-widgets">Employees</span>
                    </a>
                </li>
                @endcan
                @can('view_settings')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('settings') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <i class="ri-settings-2-fill"></i> <span data-key="t-widgets">System Setting</span>
                    </a>
                </li>
                @endcan
                @can('view_report')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('reports') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                        <i class="ri-file-text-fill"></i> <span data-key="t-widgets">OA Leads Daily Find</span>
                    </a>
                </li>
                @endcan

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
