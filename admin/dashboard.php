<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Dashboard";
require_once 'includes/header.php';

$stats = get_dashboard_stats();
?>

            <!-- Dashboard Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Total Projects -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[#5C8374] text-sm">Total Projects</p>
                            <p class="text-3xl font-bold text-[#D3DAD9]"><?php echo $stats['total_projects']; ?></p>
                        </div>
                        <div class="text-[#5C8374] text-4xl opacity-20">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Ongoing Projects -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[#5C8374] text-sm">Ongoing Projects</p>
                            <p class="text-3xl font-bold text-[#D3DAD9]"><?php echo $stats['ongoing_projects']; ?></p>
                        </div>
                        <div class="text-[#5C8374] text-4xl opacity-20">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Completed Projects -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[#5C8374] text-sm">Completed Projects</p>
                            <p class="text-3xl font-bold text-[#D3DAD9]"><?php echo $stats['completed_projects']; ?></p>
                        </div>
                        <div class="text-[#5C8374] text-4xl opacity-20">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <!-- New Inquiries -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[#5C8374] text-sm">New Inquiries (24h)</p>
                            <p class="text-3xl font-bold text-[#D3DAD9]"><?php echo $stats['new_inquiries']; ?></p>
                        </div>
                        <div class="text-[#5C8374] text-4xl opacity-20">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Active Services -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[#5C8374] text-sm">Active Services</p>
                            <p class="text-3xl font-bold text-[#D3DAD9]"><?php echo $stats['active_services']; ?></p>
                        </div>
                        <div class="text-[#5C8374] text-4xl opacity-20">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Total Users -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[#5C8374] text-sm">Team Members</p>
                            <p class="text-3xl font-bold text-[#D3DAD9]"><?php echo $stats['total_users']; ?></p>
                        </div>
                        <div class="text-[#5C8374] text-4xl opacity-20">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quick Add Project -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <h3 class="text-xl font-bold text-[#D3DAD9] mb-4">
                        <i class="fas fa-plus-circle mr-2 text-[#5C8374]"></i>Quick Actions
                    </h3>
                    <div class="space-y-2">
                        <a href="projects.php?action=new" class="block w-full px-4 py-2 bg-[#5C8374] text-[#D3DAD9] rounded-lg hover:bg-[#715A5A] transition text-center">
                            <i class="fas fa-plus mr-2"></i>Add New Project
                        </a>
                        <a href="contact_information.php?action=new" class="block w-full px-4 py-2 bg-[#5C8374] text-[#D3DAD9] rounded-lg hover:bg-[#715A5A] transition text-center">
                            <i class="fas fa-plus mr-2"></i>Add Contact Information
                        </a>
                        <a href="inquiries.php" class="block w-full px-4 py-2 bg-[#5C8374] text-[#D3DAD9] rounded-lg hover:bg-[#715A5A] transition text-center">
                            <i class="fas fa-envelope mr-2"></i>View Inquiries
                        </a>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-[#183D3D] rounded-lg p-6 shadow-lg">
                    <h3 class="text-xl font-bold text-[#D3DAD9] mb-4">
                        <i class="fas fa-history mr-2 text-[#5C8374]"></i>Recent Activity
                    </h3>
                    <div class="space-y-2 text-sm">
                        <p class="text-[#D3DAD9]">
                            <i class="fas fa-check text-green-400 mr-2"></i>
                            Dashboard loaded successfully
                        </p>
                        <p class="text-[#715A5A]">
                            <i class="fas fa-info text-blue-400 mr-2"></i>
                            All systems operational
                        </p>
                    </div>
                </div>
            </div>

<?php require_once 'includes/footer.php'; ?>
