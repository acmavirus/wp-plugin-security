<?php
// Copyright by AcmaTvirus
if (!defined('ABSPATH')) exit;
?>

<div class="grid grid-cols-12 gap-6 mb-8">
    <!-- Security Score (Growth Style) -->
    <div class="col-span-12 lg:col-span-4 dark-glass-card rounded-[40px] p-8 flex flex-col justify-between shadow-2xl">
        <div>
            <div class="flex justify-between items-start mb-6">
                <span class="text-gray-400 font-bold text-[10px] uppercase tracking-widest">Security Score</span>
                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                    <span class="dashicons dashicons-shield text-white !text-sm !w-auto !h-auto"></span>
                </div>
            </div>
            <div class="flex items-baseline gap-2 mb-4">
                <span class="text-7xl font-bold tracking-tighter"><?php echo $security_score; ?></span>
                <span class="text-gray-400 text-xl font-medium">/100</span>
            </div>
            <div class="w-full h-1.5 bg-white/10 rounded-full mb-4 overflow-hidden">
                <div class="h-full <?php echo $security_score > 70 ? 'bg-green-400' : ($security_score > 40 ? 'bg-yellow-400' : 'bg-red-400'); ?>" style="width: <?php echo $security_score; ?>%"></div>
            </div>
            <p class="text-[9px] text-gray-400 uppercase tracking-[0.2em] font-black">Health Status</p>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-10">
            <div class="bg-white/5 p-4 rounded-3xl text-center backdrop-blur-sm">
                <div class="text-xl font-bold">32</div>
                <div class="text-[8px] text-gray-500 uppercase font-bold mt-1">Files Scan</div>
            </div>
            <div class="bg-white/5 p-4 rounded-3xl text-center backdrop-blur-sm">
                <div class="text-xl font-bold"><?php echo $blocked_count; ?></div>
                <div class="text-[8px] text-gray-500 uppercase font-bold mt-1">Blocked</div>
            </div>
            <div class="bg-white/5 p-4 rounded-3xl text-center border border-white/10">
                <div class="text-xl font-bold"><?php echo $security_score; ?>%</div>
                <div class="text-[8px] text-gray-500 uppercase font-bold mt-1">Rating</div>
            </div>
        </div>
    </div>

    <!-- Weekly Status (Placeholder for Chart) -->
    <div class="col-span-12 lg:col-span-4 glass-card rounded-[40px] p-8 shadow-sm">
         <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-sm">Weekly process</h4>
            <span class="dashicons dashicons-chart-area text-black"></span>
        </div>
        <div class="flex gap-4 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-black"></div>
                <span class="text-[10px] font-bold uppercase tracking-wider">Threats</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blocked</span>
            </div>
        </div>
        <div class="h-40 flex items-end gap-2.5 px-2">
            <?php for($i=1; $i<=7; $i++): $h = rand(30, 95); ?>
                <div class="flex-grow bg-black group relative rounded-xl transition-all hover:bg-gray-800 cursor-pointer" style="height: <?php echo $h; ?>%;">
                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-black text-white text-[9px] font-black px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity shadow-xl">
                        <?php echo $h; ?>%
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        <div class="flex justify-between mt-6 px-1 text-[9px] font-black text-gray-400 uppercase tracking-widest">
             <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span class="w-5 h-5 rounded-full bg-black text-white flex items-center justify-center -mt-1">Sun</span>
        </div>
    </div>

    <!-- Month Progress -->
    <div class="col-span-12 lg:col-span-4 glass-card rounded-[40px] p-8">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-sm">System Status</h4>
            <span class="dashicons dashicons-external text-black"></span>
        </div>
        <div class="mb-10">
            <span class="text-4xl font-bold"><?php echo $security_score; ?>%</span>
            <span class="text-[10px] text-gray-400 font-bold ml-2 uppercase tracking-widest">Hardened*</span>
        </div>
        
        <div class="space-y-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-black"></div>
                    <span class="text-xs font-bold">Audit Service</span>
                </div>
                <span class="text-[10px] font-black text-green-500 uppercase">Active</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                    <span class="text-xs font-bold">WAF Firewall</span>
                </div>
                <span class="text-[10px] font-black text-green-500 uppercase">Enabled</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    <span class="text-xs font-bold text-gray-400">Malware Scan</span>
                </div>
                <span class="text-[10px] font-black text-gray-300 uppercase">Idle</span>
            </div>
        </div>

        <button class="w-full mt-10 border-2 border-black py-4 rounded-2xl font-bold text-[10px] hover:bg-black hover:text-white transition-all uppercase tracking-widest shadow-lg active:scale-95">Download PDF Report</button>
    </div>
</div>
