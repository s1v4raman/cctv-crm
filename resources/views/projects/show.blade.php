<x-app-layout>
    @php $qty = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.'); @endphp
    <x-slot name="header">
        <div>
            <a href="{{ route('projects.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $project->title }}</h2>
                    @php
                        $projStatusColor = 'var(--crm-accent, #2563eb)';
                        if ($project->status === 'completed') $projStatusColor = '#16a34a';
                        elseif (in_array($project->status, ['cancelled', 'on_hold'])) $projStatusColor = '#dc2626';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $projStatusColor }};">
                        {{ $project->status_label ?? ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if(auth()->user()->isAdmin() || ($project->created_by === auth()->id() && in_array($project->status, ['draft', 'pending_approval'])))
                        <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">Edit Details</a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('projects.updateStatus', $project) }}" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <select aria-label="Project status" name="status" onchange="this.form.submit()" class="crm-select text-xs py-1" style="height: 38px;">
                                @foreach(['draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'on_hold' => 'On Hold', 'cancelled' => 'Cancelled'] as $sVal => $sLbl)
                                    <option value="{{ $sVal }}" {{ $project->status === $sVal ? 'selected' : '' }}>{{ $sLbl }}</option>
                                @endforeach
                            </select>
                        </form>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" data-confirm="Are you sure you want to delete this project? All associated records will be removed." class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6"
         x-data="{
             currentTab: '{{ $activeTab }}',
             activeTaskView: '{{ $activeTaskView ?? 'list' }}',
             taskSearchQuery: '',
             approveModalOpen: false,
             rejectModalOpen: false,
             materialModalOpen: false,
             consumeModalOpen: false,
             consumeStep: 1,
             consumeStep1Error: '',
             consumeTaskId: '',
             consumeTaskTitle: '',
             consumeMaterialId: '',
             consumeProductId: '',
             consumeItemName: '',
             consumeQty: '',
             consumeUnit: 'meters',
             consumeWastage: '0',
             consumeDrumNo: '',
             consumeDeductStock: true,
             consumeNotes: '',
             documentModalOpen: false,
             workDayModalOpen: false,
             workerPhotoModalUrl: null,
             deviceModalOpen: false,
             bulkDeviceModalOpen: false,
             templateModalOpen: false,
             taskModalOpen: false,
             blockerModalOpen: false,
             blockerTaskId: null,
             blockerTaskTitle: '',
             blockerReason: '',
             blockerSubmitting: false,
             blockerAlertUrl: null,
             editTaskModalOpen: false,
             editTaskId: null,
             editTaskTitle: '',
             editTaskPhase: '',
             editTaskDescription: '',
             editTaskPriority: 'medium',
             editTaskStatus: 'pending',
             editTaskAssignedTo: '',
             editTaskStartDate: '',
             editTaskDueDate: '',
             editTaskEstHours: '',
             editTaskDependsOn: '',
             editTaskBlockerReason: '',
             selectedTemplateKey: '{{ array_key_first($workflowTemplates) ?? 'cctv_commercial' }}',
             activePhaseFilter: 'all',

             // Quick common CCTV blocker preset reasons
             quickBlockers: [
                 'Cannot start Camera Mounting until Client Finishes False Ceiling Paint',
                 'Waiting for Civil Contractor conduit pipe laying and coring',
                 'Client IT department IP pool allocation & VLAN tagging pending',
                 'Site electrical main power supply down / DG backup unavailable',
                 'Awaiting hardware dispatch / NVR & 4K Camera delivery from supplier',
                 'Server rack room locked / Site engineer access permit delayed'
             ],

             openBlockerModal(taskId, taskTitle, prefill = '') {
                 this.blockerTaskId = taskId;
                 this.blockerTaskTitle = taskTitle || 'Project Task';
                 this.blockerReason = prefill || 'Cannot start Camera Mounting until Client Finishes False Ceiling Paint';
                 this.blockerAlertUrl = null;
                 this.blockerModalOpen = true;
             },

             async submitBlockerModal() {
                 if (!this.blockerTaskId || !this.blockerReason.trim()) {
                     crmAlert('Please specify a blocker reason before submitting.', 'error');
                     return;
                 }
                 this.blockerSubmitting = true;
                 try {
                     const res = await fetch(`/projects/{{ $project->id }}/tasks/${this.blockerTaskId}/flag-blocker`, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({ blocker_reason: this.blockerReason.trim() })
                     });
                     const data = await res.json();
                     if (data.success) {
                         if (data.whatsapp_url) {
                             this.blockerAlertUrl = data.whatsapp_url;
                             window.open(data.whatsapp_url, '_blank');
                         }
                         window.location.reload();
                     } else {
                         crmAlert(data.message || 'Error flagging blocker', 'error');
                     }
                 } catch (e) {
                     console.error('Blocker flag error:', e);
                     crmAlert('Network error while flagging blocker', 'error');
                 } finally {
                     this.blockerSubmitting = false;
                 }
             },

             async unblockTask(taskId) {
                 if (!(await crmConfirm('Mark this task as Unblocked and resume execution to In Progress?'))) {
                     return;
                 }
                 try {
                     const res = await fetch(`/projects/{{ $project->id }}/tasks/${taskId}/unblock`, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         }
                     });
                     const data = await res.json();
                     if (data.success) {
                         window.location.reload();
                     } else {
                         crmAlert(data.message || 'Error unblocking task', 'error');
                     }
                 } catch (e) {
                     console.error('Unblock error:', e);
                 }
             },

             openEditTaskModal(task) {
                 this.editTaskId = task.id;
                 this.editTaskTitle = task.title || '';
                 this.editTaskPhase = task.phase_name || '';
                 this.editTaskDescription = task.description || '';
                 this.editTaskPriority = task.priority || 'medium';
                 this.editTaskStatus = task.status || 'pending';
                 this.editTaskAssignedTo = task.assigned_to || '';
                 this.editTaskStartDate = task.start_date ? task.start_date.substring(0, 10) : '';
                 this.editTaskDueDate = task.due_date ? task.due_date.substring(0, 10) : '';
                 this.editTaskEstHours = task.estimated_hours || '';
                 this.editTaskDependsOn = task.depends_on_task_id || '';
                 this.editTaskBlockerReason = task.blocker_reason || '';
                 this.editTaskModalOpen = true;
             },

             openConsumeModal(taskId = null, taskTitle = '', materialId = null, itemName = '', unit = 'meters', productId = '') {
                 this.consumeStep = 1;
                 this.consumeStep1Error = '';
                 this.consumeTaskId = taskId || '{{ $project->tasks->isNotEmpty() ? $project->tasks->first()->id : '' }}';
                 this.consumeTaskTitle = taskTitle || '';
                 this.consumeMaterialId = materialId || '';
                 this.consumeProductId = productId || '';
                 this.consumeItemName = itemName || '';
                 this.consumeQty = '';
                 this.consumeUnit = unit || 'meters';
                 this.consumeWastage = '0';
                 this.consumeDrumNo = '';
                 this.consumeDeductStock = true;
                 this.consumeNotes = '';
                 this.consumeModalOpen = true;
             },

             nextConsumeStep() {
                 this.consumeStep1Error = '';
                 @if($project->tasks->isNotEmpty())
                     if (!this.consumeTaskId) {
                         this.consumeStep1Error = 'Please select an associated project task.';
                         return;
                     }
                 @endif
                 if (!this.consumeItemName || !this.consumeItemName.trim()) {
                     this.consumeStep1Error = 'Please enter a material / cable description.';
                     return;
                 }
                 if (!this.consumeQty || parseFloat(this.consumeQty) <= 0) {
                     this.consumeStep1Error = 'Please enter a valid quantity used (greater than 0).';
                     return;
                 }
                 this.consumeStep = 2;
             },

             onBomSelectChange(evt) {
                 const opt = evt.target.selectedOptions[0];
                 if (opt && opt.dataset.itemName) {
                     this.consumeItemName = opt.dataset.itemName;
                     this.consumeUnit = opt.dataset.unit || 'meters';
                     this.consumeProductId = opt.dataset.productId || '';
                 }
             },

             // Device password reveal state
             revealedPasswords: {},
             async revealPassword(deviceId) {
                 if (this.revealedPasswords[deviceId]) {
                     delete this.revealedPasswords[deviceId];
                     return;
                 }
                 try {
                     const res = await fetch(`/projects/{{ $project->id }}/devices/${deviceId}/reveal`, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         }
                     });
                     const data = await res.json();
                     if (data.success) {
                         this.revealedPasswords[deviceId] = data.password;
                     }
                 } catch (e) {
                     crmAlert('Error revealing password', 'error');
                 }
             },

             // Checklist item AJAX toggle
             async toggleChecklist(checklistId, checkboxEl) {
                 try {
                     const res = await fetch(`/tasks/checklists/${checklistId}/toggle`, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         }
                     });
                     const data = await res.json();
                     if (data.success) {
                         const label = document.getElementById(`checklist-label-${checklistId}`);
                         if (label) {
                             if (data.is_completed) {
                                 label.classList.add('line-through', 'text-slate-400');
                                 label.classList.remove('text-slate-700', 'dark:text-slate-300');
                             } else {
                                 label.classList.remove('line-through', 'text-slate-400');
                                 label.classList.add('text-slate-700', 'dark:text-slate-300');
                             }
                         }
                         const progressEl = document.getElementById(`task-progress-${data.task_id}`);
                         if (progressEl) {
                             progressEl.innerText = `${data.progress.completed}/${data.progress.total} (${data.progress.percentage}%)`;
                         }
                     }
                 } catch (e) {
                     console.error('Error toggling checklist:', e);
                 }
             },

             // Quick task status change
             async updateTaskStatus(taskId, selectEl) {
                 const newStatus = selectEl.value;
                 if (newStatus === 'blocked') {
                     this.openBlockerModal(taskId, selectEl.dataset.taskTitle || 'Task', 'Cannot start Camera Mounting until Client Finishes False Ceiling Paint');
                     return;
                 }
                 try {
                     const res = await fetch(`/projects/{{ $project->id }}/tasks/${taskId}/status`, {
                         method: 'PATCH',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({ status: newStatus })
                     });
                     const data = await res.json();
                     if (data.success) {
                         window.location.reload();
                     }
                 } catch (e) {
                     console.error('Error updating status:', e);
                 }
             },

             // Initialize Sortable.js drag-and-drop across the 5 Kanban columns
             initSortableKanban() {
                 this.$nextTick(() => {
                     if (typeof Sortable === 'undefined') {
                         console.warn('Sortable.js library not detected');
                         return;
                     }
                     const columns = document.querySelectorAll('.kanban-sortable-list');
                     columns.forEach(col => {
                         new Sortable(col, {
                             group: 'kanban-board',
                             animation: 200,
                             ghostClass: 'bg-blue-50/80 dark:bg-blue-950/40 border-2 border-dashed border-blue-400 rounded-xl',
                             chosenClass: 'opacity-70 scale-[1.01]',
                             dragClass: 'shadow-2xl opacity-90 rotate-1',
                             handle: '.kanban-drag-handle',
                             onEnd: async (evt) => {
                                 const taskId = evt.item.dataset.taskId;
                                 const taskTitle = evt.item.dataset.taskTitle;
                                 const newStatus = evt.to.dataset.status;
                                 const oldStatus = evt.from.dataset.status;

                                 if (newStatus === oldStatus && evt.newIndex === evt.oldIndex) {
                                     return;
                                 }

                                 if (newStatus === 'blocked' && oldStatus !== 'blocked') {
                                     // Return element to original spot and open blocker modal
                                     evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                                     this.openBlockerModal(taskId, taskTitle, 'Cannot start Camera Mounting until Client Finishes False Ceiling Paint');
                                     return;
                                 }

                                 try {
                                     const res = await fetch(`/projects/{{ $project->id }}/tasks/${taskId}/status`, {
                                         method: 'PATCH',
                                         headers: {
                                             'Content-Type': 'application/json',
                                             'Accept': 'application/json',
                                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                         },
                                         body: JSON.stringify({ status: newStatus })
                                     });
                                     const data = await res.json();
                                     if (data.success) {
                                         window.location.reload();
                                     }
                                 } catch (err) {
                                     console.error('Drag status change error:', err);
                                 }
                             }
                         });
                     });
                 });
             }
         }"
         x-init="initSortableKanban(); $watch('currentTab', (v) => { const u = new URL(window.location); u.searchParams.set('tab', v); history.replaceState(null, '', u); }); $watch('activeTaskView', (v) => { const u = new URL(window.location); u.searchParams.set('view', v); history.replaceState(null, '', u); })">

        {{-- Flash Alerts --}}
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <x-close-button size="sm" tone="inherit" label="Dismiss message" onclick="this.parentElement.remove()" />
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <x-close-button size="sm" tone="inherit" label="Dismiss message" onclick="this.parentElement.remove()" />
            </div>
        @endif

        {{-- Admin Approval / Rejection Banner --}}
        @if($project->status === 'pending_approval')
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700/60 shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-amber-900 dark:text-amber-200 text-sm">Waiting for admin approval</h4>
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                            Created by {{ $project->creator?->name ?? 'Staff' }} on {{ $project->created_at->format('d M Y') }}. The admin sets the executing company and cabling rate when approving.
                        </p>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="rejectModalOpen = true" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 text-rose-600 border border-rose-300 dark:border-rose-800 hover:bg-rose-50 transition">
                            Reject
                        </button>
                        <button type="button" @click="approveModalOpen = true" class="px-4 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition">
                            Approve
                        </button>
                    </div>
                @endif
            </div>
        @elseif($project->status === 'rejected')
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs">
                <strong>Project Rejected:</strong> {{ $project->rejection_reason ?? 'No reason recorded.' }}
            </div>
        @endif

        {{-- 6-Tab Navigation Bar --}}
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/80 dark:border-slate-800 px-6 pt-3 shadow-xs overflow-hidden">
            <nav class="flex items-center space-x-6 text-sm font-bold overflow-x-auto whitespace-nowrap scrollbar-none">
                <button type="button" @click="currentTab = 'overview'" :class="currentTab === 'overview' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3 font-extrabold' : 'text-slate-500 dark:text-slate-400 pb-3 hover:text-slate-800 dark:hover:text-slate-200 transition-colors'" class="inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <span>Overview</span>
                </button>
                <button type="button" @click="currentTab = 'materials'" :class="currentTab === 'materials' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3 font-extrabold' : 'text-slate-500 dark:text-slate-400 pb-3 hover:text-slate-800 dark:hover:text-slate-200 transition-colors'" class="inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <span>Materials</span> <span class="text-slate-400 font-normal">{{ $project->materials->count() }}</span>
                    @if($totalCableMeters > 0)
                        <span class="px-1.5 py-0.5 text-[11px] rounded-full font-bold" style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.12); color: var(--crm-accent, #2563eb);">{{ number_format($totalCableMeters, 0) }}m</span>
                    @endif
                </button>
                <button type="button" @click="currentTab = 'documents'" :class="currentTab === 'documents' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3 font-extrabold' : 'text-slate-500 dark:text-slate-400 pb-3 hover:text-slate-800 dark:hover:text-slate-200 transition-colors'" class="inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <span>Documents</span> <span class="text-slate-400 font-normal">{{ $project->documents->count() }}</span>
                    @if($uninvoicedDcCount > 0)
                        <span class="px-1.5 py-0.5 text-[11px] rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 font-bold">{{ $uninvoicedDcCount }} unbilled</span>
                    @endif
                </button>
                <button type="button" @click="currentTab = 'work_days'" :class="currentTab === 'work_days' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3 font-extrabold' : 'text-slate-500 dark:text-slate-400 pb-3 hover:text-slate-800 dark:hover:text-slate-200 transition-colors'" class="inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <span>Work days</span> <span class="text-slate-400 font-normal">{{ $project->workDays->count() }}</span>
                </button>
                <button type="button" @click="currentTab = 'ip_devices'" :class="currentTab === 'ip_devices' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3 font-extrabold' : 'text-slate-500 dark:text-slate-400 pb-3 hover:text-slate-800 dark:hover:text-slate-200 transition-colors'" class="inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <span>IP devices</span> <span class="text-slate-400 font-normal">{{ $project->ipDevices->count() }}</span>
                </button>
                <button type="button" @click="currentTab = 'tasks'" :class="currentTab === 'tasks' ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 pb-3 font-extrabold' : 'text-slate-500 dark:text-slate-400 pb-3 hover:text-slate-800 dark:hover:text-slate-200 transition-colors'" class="inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <span>Tasks</span> <span class="text-slate-400 font-normal">{{ $tasksCount }}</span>
                    @if($tasksCount > 0)
                        <span class="px-1.5 py-0.5 text-[11px] rounded-full font-bold" style="{{ $taskCompletionPercentage === 100 ? 'background-color: rgba(16, 185, 129, 0.15); color: #10b981;' : 'background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.12); color: var(--crm-accent, #2563eb);' }}">{{ $taskCompletionPercentage }}%</span>
                    @endif
                </button>
            </nav>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: OVERVIEW                                                  --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left 2 Cols: Site, Requirements, Finance --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Site Location Card --}}
                    @if($project->site)
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    Site
                                </h3>
                                <a href="{{ $project->site->navigate_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Directions</span>
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-xs text-slate-400 block">Site Name</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $project->site->name }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Client Contact</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->site->client_name ?? '—' }} ({{ $project->site->client_phone ?? 'No phone' }})</span>
                                </div>
                                <div class="sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Address</span>
                                    <span class="text-slate-700 dark:text-slate-300">{{ $project->site->address }}, {{ $project->site->city }} {{ $project->site->pincode }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Scope & Technical Requirements --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                @if($project->project_type === 'software_web')
                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                                    <span>Web Engineering &amp; Application Scope</span>
                                @elseif($project->project_type === 'networking')
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span>Network Cabling &amp; Infrastructure Scope</span>
                                @elseif($project->project_type === 'access_control')
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    <span>Access Control &amp; Security Scope</span>
                                @elseif($project->project_type === 'hardware_attendance')
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                    <span>Scope</span>
                                @elseif($project->project_type === 'hybrid')
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    <span>Scope</span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    <span>Scope</span>
                                @endif
                            </h3>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/70 dark:border-slate-700">
                                {{ $project->project_type_label }}
                            </span>
                        </div>

                        @php $scopeFilled = collect($project->requirements ?? [])->filter(fn ($v) => filled($v))->isNotEmpty(); @endphp
                        @if($scopeFilled)
                        {{-- Dynamic Scope Metric Grid based on Project Type --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            @if($project->project_type === 'software_web')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Architecture</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $project->requirements['app_architecture'] ?? 'Modular Web App' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Primary Tech Stack</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $project->requirements['tech_stack'] ?? 'Laravel, Tailwind, Alpine' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Cloud Hosting</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['cloud_hosting'] ?? 'Cloud Server' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Core Modules</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['core_modules'] ?? 'Auth, Dashboard, REST API' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Domain &amp; SSL</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['domain_ssl'] ?? 'Configured' }}</span>
                                </div>

                            @elseif($project->project_type === 'networking')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Network Drops</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['network_drops']) ? $project->requirements['network_drops'] . ' drops' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Wi-Fi Access Points</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['wifi_access_points']) ? $project->requirements['wifi_access_points'] . ' aps' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Est. Cabling</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['cabling_metres']) ? $project->requirements['cabling_metres'] . ' m' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Server Rack Size</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['server_rack_size'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Switch Specification</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['switch_spec'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Router / Gateway</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['router_gateway'] ?? 'Not set' }}</span>
                                </div>

                            @elseif($project->project_type === 'access_control')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Controlled Doors</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['doors_count']) ? $project->requirements['doors_count'] . ' doors' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Auth Method</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $project->requirements['auth_method'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Lock Hardware</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['lock_type'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Exit Device</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['exit_device'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Controller Spec</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['controller_spec'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Power Backup</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['backup_battery'] ?? 'Not set' }}</span>
                                </div>

                            @elseif($project->project_type === 'hardware_attendance')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">User Capacity</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->requirements['user_capacity'] ?? ($project->requirements['terminal_users'] ?? 'N/A') }} Users</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Comm. Protocol</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['comm_protocol'] ?? ($project->requirements['communication'] ?? 'TCP/IP Network') }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">HRMS Sync</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['hrms_software'] ?? 'Automated Sync' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Mounting Location</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['mounting_location'] ?? 'Entry / Reception' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Battery &amp; Backup</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['power_backup'] ?? ($project->requirements['power_battery'] ?? 'Internal Battery') }}</span>
                                </div>

                            @elseif($project->project_type === 'hybrid')
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Hardware Endpoints</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['hardware_endpoints']) ? $project->requirements['hardware_endpoints'] . ' devices' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Edge Gateway</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['edge_gateway'] ?? 'IoT Hub' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Storage / Server</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['server_storage'] ?? 'Micro-Server' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block">Software Platform</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['software_platform'] ?? 'Web & Mobile' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Alert Channels</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['alert_channels'] ?? 'Real-time' }}</span>
                                </div>

                            @else
                                {{-- Default CCTV Surveillance Scope --}}
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">CCTV Cameras</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['cctv_count']) ? $project->requirements['cctv_count'] . ' nos' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">NVR Channels</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['nvr_channels']) ? $project->requirements['nvr_channels'] . ' ch' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Est. Cabling</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ isset($project->requirements['cabling_metres']) ? $project->requirements['cabling_metres'] . ' m' : 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Power Supply</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['power_supply'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Storage HDD</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['storage_hdd'] ?? 'Not set' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block">Display / Screen</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->requirements['display_screen'] ?? 'Not set' }}</span>
                                </div>
                            @endif
                        </div>
                        @else
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Scope not filled in yet.
                                @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}" class="action-link">Add the scope</a>
                                @endcan
                            </p>
                        @endif

                        {{-- Additional dynamic custom attributes if any other keys exist in requirements --}}
                        @php
                            $knownKeys = [
                                'cctv_count', 'nvr_channels', 'cabling_metres', 'power_supply', 'display_screen', 'storage_hdd',
                                'network_drops', 'wifi_access_points', 'server_rack_size', 'switch_spec', 'router_gateway',
                                'app_architecture', 'tech_stack', 'cloud_hosting', 'core_modules', 'domain_ssl',
                                'doors_count', 'auth_method', 'lock_type', 'exit_device', 'controller_spec', 'backup_battery',
                                'user_capacity', 'comm_protocol', 'hrms_software', 'mounting_location', 'power_backup',
                                'hardware_endpoints', 'software_platform', 'edge_gateway', 'server_storage', 'alert_channels',
                                'special_requirements'
                            ];
                            $customRequirements = is_array($project->requirements) 
                                ? array_diff_key($project->requirements, array_flip($knownKeys))
                                : [];
                        @endphp

                        @if(!empty($customRequirements))
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Custom Scope Specifications</span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($customRequirements as $cKey => $cVal)
                                        <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[11px] text-slate-400 uppercase block font-semibold">{{ ucwords(str_replace('_', ' ', $cKey)) }}</span>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ is_array($cVal) ? json_encode($cVal) : $cVal }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($project->requirements['special_requirements']) || $project->description)
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
                                <span class="font-bold text-slate-400 block mb-1">Client Special Instructions:</span>
                                <p class="whitespace-pre-line">{{ $project->requirements['special_requirements'] ?? $project->description }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Software & Web Application Portal Panel --}}
                    @if($project->project_type === 'software_web' || $project->project_type === 'hybrid' || !empty($project->software_specs['webpage_url']) || !empty($project->software_specs['repository_url']))
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <span class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                                    <span>Web Application &amp; Deployment Links</span>
                                </span>
                                @if(!empty($project->software_specs['webpage_url']))
                                    <a href="{{ $project->software_specs['webpage_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-primary text-xs py-1 px-2.5 flex items-center gap-1 shadow-xs">
                                        <span>Open Live Site</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @endif
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Live / Production Webpage</span>
                                    @if(!empty($project->software_specs['webpage_url']))
                                        <a href="{{ $project->software_specs['webpage_url'] }}" target="_blank" rel="noopener noreferrer" class="font-mono text-cyan-600 dark:text-cyan-400 hover:underline break-all">
                                            {{ $project->software_specs['webpage_url'] }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Not set</span>
                                    @endif
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Source Repository</span>
                                    @if(!empty($project->software_specs['repository_url']))
                                        <a href="{{ $project->software_specs['repository_url'] }}" target="_blank" rel="noopener noreferrer" class="font-mono text-blue-600 dark:text-blue-400 hover:underline break-all">
                                            {{ $project->software_specs['repository_url'] }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Not set</span>
                                    @endif
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Primary Tech Stack</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->software_specs['tech_stack'] ?? ($project->requirements['tech_stack'] ?? 'Laravel & Tailwind') }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-[11px] text-slate-400 block font-semibold mb-1">Deployment Infrastructure</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->software_specs['deployment_server'] ?? ($project->requirements['cloud_hosting'] ?? 'Cloud Hosting') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Hardware Terminal & Attendance Specs --}}
                    @if($project->project_type === 'hardware_attendance' || !empty($project->hardware_specs['terminal_count']) || !empty($project->hardware_specs['camera_count']) || !empty($project->hardware_specs['device_brand']))
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <span class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    <span>Terminal &amp; Camera Hardware Specifications</span>
                                </span>
                                <button type="button" @click="currentTab = 'ip_devices'" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline flex items-center gap-1">
                                    <span>Open IP Devices Register ({{ $project->ipDevices->count() }}) &rarr;</span>
                                </button>
                            </h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Terminals Count</span>
                                    <span class="font-extrabold text-indigo-600 dark:text-indigo-400 text-base">{{ $project->hardware_specs['terminal_count'] ?? 0 }} Units</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Cameras Count</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white text-base">{{ $project->hardware_specs['camera_count'] ?? 0 }} Units</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Device Brand / Model</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $project->hardware_specs['device_brand'] ?? 'ZKTeco / Hikvision' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-xs text-slate-400 block font-medium">Terminal IP Subnet</span>
                                    <span class="font-mono text-slate-800 dark:text-slate-200 text-xs">{{ $project->hardware_specs['terminal_ip'] ?? 'DHCP / Auto' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 sm:col-span-2">
                                    <span class="text-xs text-slate-400 block font-medium">Attendance Sync Mode</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ ucwords(str_replace('_', ' ', $project->hardware_specs['attendance_sync_mode'] ?? 'AI Face Recognition')) }}</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                <button type="button" @click="deviceModalOpen = true" class="btn-secondary">
                                    Add Terminal
                                </button>
                                <a href="{{ route('attendance.index') }}" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline flex items-center gap-1">
                                    <span>View Attendance &amp; Shift Hub</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Right 1 Col: Team Assignment, Financials & Audit Log --}}
                <div class="space-y-6">

                    {{-- Assigned Team & Technical Execution Card --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: var(--crm-accent, #2563eb);"></span>
                                Project Team
                            </h3>
                            <div class="flex items-center gap-1.5">
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.users.create', ['role' => 'technician', 'return_to' => route('projects.show', [$project->id, 'tab' => 'overview'])]) }}" 
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-white text-xs font-semibold shadow-xs transition"
                                   style="background-color: var(--crm-accent, #2563eb);"
                                   title="Add and register a new technician for this project">
                                    Add Technician
                                </a>
                                @endif
                                @if(auth()->user()->isAdmin() || ($project->created_by === auth()->id() && in_array($project->status, ['draft', 'pending_approval'])))
                                    <a href="{{ route('projects.edit', $project) }}" class="text-xs font-semibold hover:underline px-1 py-0.5" style="color: var(--crm-accent, #2563eb);">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Newly Added Technician Alert --}}
                        @if(session('new_technician_id'))
                            @php
                                $newTech = \App\Models\User::find(session('new_technician_id'));
                            @endphp
                            @if($newTech && $project->lead_technician_id != $newTech->id)
                                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>New Technician <strong>{{ $newTech->name }}</strong> registered!</span>
                                    </div>
                                    <form action="{{ route('projects.assign-technician', $project) }}" method="POST" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="lead_technician_id" value="{{ $newTech->id }}">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs">
                                            Assign as Lead
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endif

                        <div class="space-y-3 text-xs">
                            {{-- Lead Technician --}}
                            <div x-data="{ showAssignForm: false }" class="p-3 rounded-xl border" style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.08); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[11px] uppercase tracking-wider block font-bold" style="color: var(--crm-accent, #2563eb);">Lead Technician (In-Charge)</span>
                                    <button type="button" @click="showAssignForm = !showAssignForm" class="text-[11px] font-semibold hover:underline" style="color: var(--crm-accent, #2563eb);">
                                        <span x-text="showAssignForm ? 'Cancel' : '{{ $project->leadTechnician ? 'Change' : '+ Assign' }}'"></span>
                                    </button>
                                </div>

                                <div x-show="!showAssignForm">
                                    @if($project->leadTechnician)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="font-bold text-slate-900 dark:text-white text-sm block">{{ $project->leadTechnician->name }}</span>
                                                <span class="text-slate-500 text-[11px]">{{ $project->leadTechnician->email }}</span>
                                            </div>
                                            @if($project->leadTechnician->phone)
                                                <a href="tel:{{ $project->leadTechnician->phone }}" class="px-2.5 py-1 rounded-lg text-white font-bold text-[11px] shadow-xs" style="background-color: var(--crm-accent, #2563eb);">
                                                    Call
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between pt-1">
                                            <span class="text-slate-500">Not assigned yet</span>
                                            @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.users.create', ['role' => 'technician', 'return_to' => route('projects.show', [$project->id, 'tab' => 'overview'])]) }}" 
                                               class="btn-subtle text-white font-semibold"
                                               style="color: #ffffff !important;">
                                                <span class="text-white font-semibold" style="color: #ffffff !important;">+ Add New Technician</span>
                                            </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Inline Quick Assign Form --}}
                                <div x-show="showAssignForm" x-cloak class="mt-2 pt-2 border-t border-blue-200/50 dark:border-blue-800/50 space-y-2">
                                    <form action="{{ route('projects.assign-technician', $project) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <select aria-label="Lead technician" name="lead_technician_id" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2 focus:ring-1 focus:ring-blue-500" required>
                                                <option value="">-- Choose Lead Technician --</option>
                                                @foreach($technicians as $tech)
                                                    <option value="{{ $tech->id }}" @selected($project->lead_technician_id == $tech->id)>
                                                        {{ $tech->name }} ({{ ucfirst($tech->role) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-center justify-between pt-1">
                                            @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.users.create', ['role' => 'technician', 'return_to' => route('projects.show', [$project->id, 'tab' => 'overview'])]) }}" 
                                               class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold hover:underline flex items-center gap-1">
                                                <span>Create New Technician</span>
                                            </a>
                                            @endif
                                            <div class="flex gap-2">
                                                <button type="button" @click="showAssignForm = false" class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs">
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Assigned Staff / Employee --}}
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 uppercase tracking-wider block font-bold mb-1">Assigned Staff / Employee</span>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white text-xs block">{{ $project->assignedUser?->name ?? 'Unassigned' }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $project->assignedUser ? ucfirst($project->assignedUser->role) : '—' }}</span>
                                    </div>
                                    @if($project->creator)
                                        <div class="text-right">
                                            <span class="text-[11px] text-slate-400 block">Created By</span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-300 text-[11px]">{{ $project->creator->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Executing Entity --}}
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                <span class="text-[11px] text-slate-400 uppercase tracking-wider block font-bold mb-1">Executing Entity</span>
                                @if($project->company)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white text-xs block">{{ $project->company->name }}</span>
                                            <span class="text-[11px] text-emerald-600 font-semibold">✓ Admin Approved</span>
                                        </div>
                                        @if($project->approved_on)
                                            <span class="text-[11px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($project->approved_on)->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-500 text-xs">Set by the admin on approval</span>
                                @endif
                            </div>
                        </div>
                    </div>


                    {{-- Audit Trail Log --}}
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-sm">History</h3>

                        <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                            @forelse($project->auditLogs as $log)
                                <div class="text-xs pl-3 py-1" style="border-left: 2px solid var(--crm-accent, #2563eb);">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $log->action_label }}</div>
                                    @if($log->reason)<div class="text-[11px] text-slate-500">{{ $log->reason }}</div>@endif
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $log->user?->name ?? 'System' }} &bull; {{ $log->created_at->diffForHumans() }}</div>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 italic">No activity logged yet.</div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: MATERIALS & BOM CONSUMPTION TRACKER                        --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'materials'" x-cloak class="space-y-6">

            {{-- Materials summary: one line until something has been used --}}
            @php
                $hardwareInstalled = $project->materialConsumptions->where('unit', '!=', 'meters')->sum('quantity_used');
                $nothingUsedYet = $totalCableMeters == 0 && $hardwareInstalled == 0 && $totalWastageQuantity == 0;
            @endphp
            <div class="rounded-lg bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 px-5 py-4">
                @if($nothingUsedYet)
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        {{ $project->materials->count() }} {{ Str::plural('item', $project->materials->count()) }} planned ({{ $qty($totalBomQuantity) }} units) · nothing used yet
                    </p>
                @else
                    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Planned</dt>
                            <dd class="text-lg font-semibold text-slate-900 dark:text-white">{{ $project->materials->count() }} {{ Str::plural('item', $project->materials->count()) }} <span class="text-sm font-normal text-slate-500">· {{ $qty($totalBomQuantity) }} units</span></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Cable pulled</dt>
                            <dd class="text-lg font-semibold text-slate-900 dark:text-white">{{ $qty($totalCableMeters) }} m</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Hardware installed</dt>
                            <dd class="text-lg font-semibold text-slate-900 dark:text-white">{{ $qty($hardwareInstalled) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Wastage</dt>
                            <dd class="text-lg font-semibold {{ $totalWastageQuantity > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">{{ $qty($totalWastageQuantity) }}</dd>
                        </div>
                    </dl>
                @endif
            </div>

            {{-- 1. Bill of Materials (BOM) & Reconciliation Table --}}
            <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <span>Materials</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">What was planned for this site and how much has been used.</p>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap">
                        <button type="button" @click="openConsumeModal()" class="btn-primary text-xs shadow-md">
                            Log usage
                        </button>
                        <button type="button" @click="materialModalOpen = true" class="btn-secondary text-xs">
                            Add item
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Material / Equipment</th>
                                <th class="px-5 py-3.5">Sourcing</th>
                                <th class="px-5 py-3.5 text-center">Allocated (BOM)</th>
                                <th class="px-5 py-3.5 text-center">Actual Consumed</th>
                                <th class="px-5 py-3.5 text-center">Remaining Balance</th>
                                <th class="px-5 py-3.5 text-center">Wastage</th>
                                <th class="px-5 py-3.5 min-w-[160px]">Installation Progress</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:border-slate-800">
                            @forelse($project->materials as $mat)
                                @php
                                    $consumed = $mat->total_consumed;
                                    $allocated = (float) $mat->quantity;
                                    $percent = $mat->consumption_percentage;
                                    $isOver = $mat->is_over_consumed;
                                    $remaining = $mat->remaining_quantity;
                                @endphp
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span>{{ $mat->item_name }}</span>
                                            @if($mat->product)
                                                <span class="px-1.5 py-0.5 rounded text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                    {{ $mat->product->sku }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($mat->notes)
                                            <span class="text-xs text-slate-400 block font-normal mt-0.5">{{ $mat->notes }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="text-sm text-slate-700 dark:text-slate-300">
                                            {{ in_array($mat->source, ['warehouse', 'company_stock']) ? 'Warehouse' : 'Local purchase' }}
                                        </span>
                                        @if($mat->shop_name)
                                            <span class="block text-[11px] text-slate-400 mt-0.5">{{ $mat->shop_name }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-bold font-mono text-slate-900 dark:text-white">
                                        {{ $qty($mat->quantity) }} <span class="text-xs font-normal text-slate-400 uppercase">{{ $mat->unit }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-bold font-mono {{ $isOver ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                        {{ $qty($consumed) }} <span class="text-xs font-normal text-slate-400 uppercase">{{ $mat->unit }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-bold font-mono">
                                        @if($isOver)
                                            <span class="text-rose-600 dark:text-rose-400 font-extrabold">+{{ $qty($consumed - $allocated) }} over</span>
                                        @else
                                            <span class="text-slate-700 dark:text-slate-300">{{ $qty($remaining) }} {{ $mat->unit }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-mono text-xs text-slate-500">
                                        {{ $qty($mat->total_wastage) }} {{ $mat->unit }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($consumed <= 0)
                                            <span class="text-xs text-slate-500 dark:text-slate-400">Not started</span>
                                        @else
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-xs font-bold">
                                                <span class="{{ $isOver ? 'text-rose-600' : ($percent >= 100 ? 'text-emerald-600' : 'text-blue-600') }}">
                                                    @if($isOver)
                                                        Over by {{ round(($consumed / $allocated) * 100) - 100 }}%
                                                    @elseif($percent >= 100)
                                                        Installed
                                                    @else
                                                        {{ $percent }}% Installed
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full transition-all {{ $isOver ? 'bg-rose-500' : ($percent >= 100 ? 'bg-emerald-500' : 'bg-blue-600') }}" style="width: {{ min(100, $percent) }}%"></div>
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" @click="openConsumeModal(null, '', {{ $mat->id }}, '{{ addslashes($mat->item_name) }}', '{{ $mat->unit }}', '{{ $mat->product_id }}')" class="action-link text-xs" title="Log usage for this item">
                                                Log usage
                                            </button>

                                            @if(auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('projects.materials.destroy', ['project' => $project, 'material' => $mat]) }}" data-confirm="Remove this material from BOM?" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <x-close-button size="sm" tone="danger" label="Delete BOM item" type="submit" />
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-xs">
                                        No materials planned yet. Use <strong>Add item</strong> to list the hardware and cable for this site.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. Chronological Field Consumption & Cable Pulling History Log --}}
            <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <span>Usage log</span>
                            <span class="px-2 py-0.5 text-[11px] rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold font-mono">
                                {{ $project->materialConsumptions->count() }} Entries Logged
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Audit log of cable pulls, meters, drum serials, and equipment consumed per specific task</p>
                    </div>

                    <button type="button" @click="openConsumeModal()" class="btn-secondary text-xs">
                        + Record Field Usage
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Date &amp; Time</th>
                                <th class="px-5 py-3.5">Associated Task</th>
                                <th class="px-5 py-3.5">Item Consumed</th>
                                <th class="px-5 py-3.5 text-center">Quantity Used</th>
                                <th class="px-5 py-3.5 text-center">Scrap / Cutoff</th>
                                <th class="px-5 py-3.5">Drum / Reel / Batch #</th>
                                <th class="px-5 py-3.5">Logged By</th>
                                <th class="px-5 py-3.5">Inventory Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:border-slate-800">
                            @forelse($project->materialConsumptions as $cLog)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-500">
                                        <span class="font-bold text-slate-700 dark:text-slate-300 block">{{ $cLog->consumed_at ? $cLog->consumed_at->format('d M Y') : $cLog->created_at->format('d M Y') }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $cLog->consumed_at ? $cLog->consumed_at->format('h:i A') : $cLog->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200 text-xs">
                                            
                                            <span>{{ $cLog->task?->title ?? 'General Project Task' }}</span>
                                        </div>
                                        @if($cLog->task?->phase_name)
                                            <span class="text-[11px] text-slate-400 block mt-0.5">{{ $cLog->task->phase_name }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">
                                        {{ $cLog->item_name }}
                                        @if($cLog->notes)
                                            <span class="text-xs text-slate-400 block font-normal mt-0.5 italic">"{{ $cLog->notes }}"</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-bold font-mono text-indigo-600 dark:text-indigo-400 text-sm">
                                        {{ $qty($cLog->quantity_used) }} <span class="text-xs uppercase text-slate-400 font-normal">{{ $cLog->unit }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-mono text-xs text-slate-500">
                                        @if($cLog->wastage_quantity > 0)
                                            <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $qty($cLog->wastage_quantity) }} {{ $cLog->unit }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-mono text-slate-700 dark:text-slate-300">
                                        {{ $cLog->drum_or_batch_no ?: '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold flex items-center justify-center">
                                                {{ strtoupper(substr($cLog->consumedBy?->name ?? 'T', 0, 1)) }}
                                            </span>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $cLog->consumedBy?->name ?? 'Technician' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs">
                                        @if($cLog->deduct_from_stock)
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                ✓ Warehouse Deducted
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                Site Direct
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <form method="POST" action="{{ route('projects.material-consumptions.destroy', ['project' => $project, 'consumption' => $cLog]) }}" data-confirm="Revert this consumption entry? If deducted from warehouse stock, inventory will be restored." class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold" title="Revert entry and restore inventory">
                                                Revert
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-12 text-center text-slate-400 text-xs">
                                        No task-level material or cable consumption logged yet. Use <strong>+ Record Field Usage</strong> or log directly from any task in the Kanban board.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: DOCUMENTS & DELIVERY CHALLANS                             --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'documents'" x-cloak class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Documents &amp; Delivery Challans</h3>
                        <p class="text-xs text-slate-500">Official dispatch challans, site blueprints, sign-offs &amp; customer invoices</p>
                    </div>

                    <button type="button" @click="documentModalOpen = true" class="btn-primary text-xs shadow-md">
                        + Upload Document / DC
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Type &amp; Title</th>
                                <th class="px-5 py-3.5">DC Number &amp; Date</th>
                                <th class="px-5 py-3.5">Items Summary</th>
                                <th class="px-5 py-3.5 text-center">Invoice Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:border-slate-800">
                            @forelse($project->documents as $doc)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $doc->title }}</div>
                                        <div class="text-xs text-slate-400">{{ $doc->document_type_label }} &bull; {{ $doc->formatted_file_size }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs">
                                        @if($doc->dc_number)
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $doc->dc_number }}</span>
                                            <div class="text-[11px] text-slate-400">{{ $doc->dc_date ? $doc->dc_date->format('d M Y') : '' }}</div>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-xs max-w-xs">
                                        {{ $doc->items_summary ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if($doc->document_type === 'delivery_challan')
                                            @if($doc->is_invoiced)
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                    Invoiced: {{ $doc->invoice_number }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                    Ready to Invoice
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('projects.documents.view', $doc) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 transition" title="Preview">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('projects.documents.download', $doc) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 transition" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                            @if(auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('projects.documents.destroy', $doc) }}" data-confirm="Delete this document?" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-slate-400 text-xs">
                                        No documents or Delivery Challans uploaded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 4: WORK DAYS & DAILY WAGES                                   --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'work_days'" x-cloak class="space-y-6">

            @if($project->workDays->isNotEmpty())
            {{-- Summary KPI Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <span class="text-[11px] font-bold uppercase text-slate-400 block">Total Man-Days</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $totalManDays }} <span class="text-xs font-semibold text-slate-400">Days</span></div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <span class="text-[11px] font-bold uppercase text-slate-400 block">Cabling Pulled</span>
                    <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">{{ $totalCablingMetres }} <span class="text-xs font-semibold">m</span></div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <span class="text-[11px] font-bold uppercase text-slate-400 block">Total Labor Cost</span>
                    <div class="text-xl font-black text-slate-900 dark:text-white mt-1 font-mono">₹{{ number_format($totalLaborCost, 2) }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                    <span class="text-[11px] font-bold uppercase text-slate-400 block">Labor Paid (Settled)</span>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">₹{{ number_format($totalLaborPaid, 2) }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold uppercase text-rose-500 dark:text-rose-400 block">Cost to Pay (Pending)</span>
                    <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1 font-mono">₹{{ number_format($totalLaborPendingPay, 2) }}</div>
                </div>
            </div>
            @endif

            {{-- Worker-wise Project Cost & Settlement Analysis Table --}}
            @if(!empty($workerAnalysis))
                <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                <span>Worker-wise Labor Cost &amp; Settlement Analysis ({{ count($workerAnalysis) }} Workers)</span>
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Breakdown of man-days, cabling metrics, wages earned, amounts settled, and pending costs to pay for this project.
                            </p>
                        </div>
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('wages.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                            <span>Open Sunday Wage Console &rarr;</span>
                        </a>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="text-xs uppercase font-bold text-slate-400 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/40">
                                <tr>
                                    <th class="px-5 py-3">Worker Name</th>
                                    <th class="px-5 py-3 text-center">Shifts / Man-Days</th>
                                    <th class="px-5 py-3 text-center">Cabling (m)</th>
                                    <th class="px-5 py-3 text-center">Cameras</th>
                                    <th class="px-5 py-3 text-right">Total Incurred</th>
                                    <th class="px-5 py-3 text-right">Amount Paid</th>
                                    <th class="px-5 py-3 text-right">Cost to Pay (Pending)</th>
                                    <th class="px-5 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($workerAnalysis as $wId => $wData)
                                    @php
                                        $wrk = $wData['worker'] ?? null;
                                        $wrkId = $wrk?->id ?? null;
                                        $wrkName = $wrk?->name ?? ('Worker #' . $wId);
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                        <td class="px-5 py-3.5">
                                            @if($wrkId)
                                                <a href="{{ route('workers.show', $wrkId) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 hover:underline block leading-tight">
                                                    {{ $wrkName }}
                                                </a>
                                            @else
                                                <span class="font-bold text-slate-900 dark:text-white block leading-tight">
                                                    {{ $wrkName }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-slate-500 font-mono">₹{{ number_format($wrk?->daily_rate ?? 0, 2) }}/day &bull; <svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ $wrk?->phone ?? '—' }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-center font-medium">
                                            {{ $wData['man_days'] }} <span class="text-xs text-slate-400">({{ $wData['shifts_count'] }} {{ Str::plural('shift', $wData['shifts_count']) }})</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-center font-mono font-semibold text-slate-900 dark:text-white">
                                            {{ $wData['cabling_metres'] > 0 ? $wData['cabling_metres'] . ' m' : '—' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-center font-semibold text-emerald-600 dark:text-emerald-400">
                                            {{ $wData['cameras_installed'] > 0 ? $wData['cameras_installed'] : '—' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            ₹{{ number_format($wData['total_earned'], 2) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                            ₹{{ number_format($wData['amount_paid'], 2) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono font-black {{ $wData['cost_to_pay'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                            ₹{{ number_format($wData['cost_to_pay'], 2) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                            @if($wData['cost_to_pay'] <= 0)
                                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                    ✓ Settled
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                    Pending ₹{{ number_format($wData['cost_to_pay'], 0) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="flex justify-between items-center pt-2 flex-wrap gap-2">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Work days</h3>
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" onclick="openAttendanceModal('{{ $project->id }}')" class="btn-primary text-xs flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>+ Mark Attendance (Photo Punch)</span>
                    </button>
                    @can('logWorkDay', $project)
                        <button type="button" @click="workDayModalOpen = true" class="btn-secondary text-xs">
                            Batch Work Day Log
                        </button>
                    @endcan
                </div>
            </div>

            <div class="space-y-4">
                @forelse($project->workDays as $wd)
                    <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                        <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="font-black text-sm font-mono text-slate-900 dark:text-white">
                                    {{ $wd->work_date->format('d M Y (l)') }}
                                </span>
                                @if($wd->notes)
                                    <span class="text-xs text-slate-500 italic">"{{ $wd->notes }}"</span>
                                @endif
                            </div>

                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('projects.work-days.destroy', ['project' => $project, 'workDay' => $wd]) }}" data-confirm="Remove this work day log?" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-semibold">Delete Work Day</button>
                            </form>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                                <thead class="text-xs uppercase font-bold text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="px-5 py-2.5">Worker &amp; Photo</th>
                                        <th class="px-5 py-2.5">Shift Type</th>
                                        <th class="px-5 py-2.5">Rate Snapshot</th>
                                        <th class="px-5 py-2.5">Cabling &amp; Cameras</th>
                                        <th class="px-5 py-2.5">Work Notes &amp; Extras</th>
                                        <th class="px-5 py-2.5 text-right">Row Total</th>
                                        <th class="px-5 py-2.5 text-center">Settlement Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($wd->attendances as $att)
                                        <tr>
                                            <td class="px-5 py-3">
                                                <div class="flex items-center gap-3">
                                                    @if($att->photo_path)
                                                        <div class="relative shrink-0">
                                                            <img src="{{ $att->photo_url }}" 
                                                                 alt="{{ $att->worker?->name ?? 'Worker' }}" 
                                                                 @click="workerPhotoModalUrl = '{{ $att->photo_url }}'"
                                                                 title="Click to view verified punch selfie"
                                                                 class="w-10 h-10 rounded-full object-cover border-2 border-emerald-500 cursor-pointer shadow-sm hover:scale-105 transition-transform">
                                                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full" title="Photo Verified"></span>
                                                        </div>
                                                    @else
                                                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center text-xs font-bold text-slate-500 shrink-0">
                                                            {{ strtoupper(substr($att->worker?->name ?? 'WK', 0, 2)) }}
                                                        </div>
                                                    @endif

                                                    <div>
                                                        <div class="font-bold text-slate-900 dark:text-white leading-tight">
                                                            @if($att->worker?->id)
                                                                <a href="{{ route('workers.show', $att->worker->id) }}" class="hover:text-blue-600 hover:underline">
                                                                    {{ $att->worker->name }}
                                                                </a>
                                                            @else
                                                                {{ $att->worker?->name ?? ('Worker #' . ($att->worker_id ?? '—')) }}
                                                            @endif
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex items-center gap-2">
                                                            <span><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ $att->worker?->phone ?? '—' }}</span>
                                                            @if($att->check_in_at)
                                                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">• {{ $att->check_in_at->format('h:i A') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3">
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $att->attendance_type === 'full_day' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' }}">
                                                    {{ $att->attendance_type === 'full_day' ? 'Full Day' : 'Half Day' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 font-mono text-xs">
                                                ₹{{ number_format($att->daily_rate_snapshot, 2) }}
                                            </td>
                                            <td class="px-5 py-3 text-xs">
                                                <div class="font-mono font-semibold text-slate-900 dark:text-white">
                                                    {{ $att->cabling_metres > 0 ? $att->cabling_metres . ' m' : '—' }}
                                                    @if($att->cabling_metres > 0)
                                                        <span class="text-slate-400 font-normal">(+₹{{ number_format($att->cabling_amount, 2) }})</span>
                                                    @endif
                                                </div>
                                                @if($att->cameras_installed > 0)
                                                    <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                                        {{ $att->cameras_installed }} cameras installed
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-xs">
                                                @if($att->extra_amount > 0)
                                                    <div class="font-semibold text-slate-800 dark:text-slate-200">
                                                        +₹{{ number_format($att->extra_amount, 2) }} ({{ $att->extra_description }})
                                                    </div>
                                                @endif
                                                @if($att->work_summary)
                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2" title="{{ $att->work_summary }}">
                                                        {{ $att->work_summary }}
                                                    </div>
                                                @elseif($att->notes)
                                                    <div class="text-[11px] text-slate-400 italic mt-0.5">
                                                        "{{ $att->notes }}"
                                                    </div>
                                                @elseif($att->extra_amount <= 0)
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right font-bold font-mono text-slate-900 dark:text-white">
                                                ₹{{ number_format($att->total_amount, 2) }}
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                @if($att->is_paid)
                                                    <div class="inline-flex items-center gap-1">
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                            Paid &amp; Locked
                                                        </span>
                                                        @if(auth()->user()->isAdmin())
                                                            <form method="POST" action="{{ route('projects.attendances.unlock', $att) }}" class="inline-block" data-confirm="Unlock this attendance record?">
                                                                @csrf
                                                                <button type="submit" class="text-[11px] text-rose-500 hover:underline">Unlock</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                            Unpaid
                                                        </span>
                                                        @if(auth()->user()->isAdmin())
                                                            <form method="POST" action="{{ route('projects.attendances.pay', $att) }}" class="inline-block" data-confirm="Disburse daily wage payout of ₹{{ number_format($att->total_amount, 2) }} now to {{ $att->worker?->name ?? 'Worker' }}?">
                                                                @csrf
                                                                <button type="submit" class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs cursor-pointer transition">
                                                                    Pay Shift (₹{{ number_format($att->total_amount, 0) }})
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 text-slate-400 text-xs">
                        No work days logged yet.@can('logWorkDay', $project) Use <strong>Log work day</strong> to record who worked on site and what they did.@endcan
                    </div>
                @endforelse
            </div>

            {{-- Worker Photo Modal Preview in Tab 4 --}}
            <div x-show="workerPhotoModalUrl" 
                 x-cloak 
                 class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
                 @click.self="workerPhotoModalUrl = null">
                <div class="relative bg-white dark:bg-[#0f172a] rounded-2xl overflow-hidden max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl">
                    <div class="p-3 bg-slate-900 text-white flex items-center justify-between">
                        <span class="text-xs font-bold">Worker Attendance Verified Selfie</span>
                        <x-close-button size="sm" tone="dark" label="Close photo" @click="workerPhotoModalUrl = null" />
                    </div>
                    <div class="p-3 bg-slate-100 dark:bg-slate-950 flex items-center justify-center">
                        <img :src="workerPhotoModalUrl" alt="Worker Punch Photo" class="max-h-[70vh] w-auto object-contain rounded-xl">
                    </div>
                    <div class="p-3 text-right bg-white dark:bg-[#0f172a] border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="workerPhotoModalUrl = null" class="px-4 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-700">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 5: IP DEVICES REGISTER                                       --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'ip_devices'" x-cloak class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Network IP Devices Register</h3>
                        <p class="text-xs text-slate-500">Camera addressing, ports, RTSP streams &amp; encrypted credentials</p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('projects.devices.export', $project) }}" class="btn-secondary text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Export CSV</span>
                        </a>
                        @if(!auth()->user()->isStaff())
                            <button type="button" @click="bulkDeviceModalOpen = true" class="btn-secondary text-xs flex items-center gap-1">
                                Bulk Sequential Generator
                            </button>
                            <button type="button" @click="deviceModalOpen = true" class="btn-secondary">
                                Add Device
                            </button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5">Device Name</th>
                                <th class="px-5 py-3.5">IP Address</th>
                                <th class="px-5 py-3.5">Ports</th>
                                <th class="px-5 py-3.5">Username</th>
                                <th class="px-5 py-3.5">Password</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($project->ipDevices as $dev)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $dev->device_name }}</div>
                                        <div class="text-xs text-slate-400">{{ $dev->device_type ?? 'Camera' }} &bull; {{ $dev->location ?? 'Main Area' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono font-bold text-xs text-blue-600 dark:text-blue-400">
                                        {{ $dev->ip_address }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-mono">
                                        Web: {{ $dev->web_port ?? 80 }} &bull; RTSP: {{ $dev->rtsp_port ?? 554 }}
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs">
                                        {{ $dev->username ?? 'admin' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs font-mono">
                                        <div class="flex items-center gap-2">
                                            <span x-text="revealedPasswords[{{ $dev->id }}] ? revealedPasswords[{{ $dev->id }}] : '••••••••'"></span>
                                            <button type="button" @click="revealPassword({{ $dev->id }})" class="text-slate-400 hover:text-blue-600 cursor-pointer" title="Reveal Password (Logged to Audit Trail)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                            {{ ucfirst($dev->status ?? 'Configured') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('projects.devices.destroy', ['project' => $project, 'device' => $dev]) }}" data-confirm="Delete this IP device?" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                                                Delete
                                            </button>
                                        </form>
                                        @else
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-slate-400 text-xs">
                                        No IP devices configured yet. Use "Bulk Sequential Generator" to auto-populate camera IPs.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 6: TASKS & WORKFLOW ENGINE (KANBAN & GANTT)                  --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'tasks'" class="space-y-6">

            {{-- Flash WhatsApp Blocker Alert Banner --}}
            @if(session('whatsapp_blocker_url'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-md animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl shrink-0"><svg class="w-5 h-5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></span>
                    <div>
                        <strong class="font-extrabold text-sm block">Task Blocked Alert Dispatched to Dashboard &amp; WhatsApp</strong>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">The blocker has been logged. Click below to launch WhatsApp and notify the Project Manager with the pre-formatted alert.</p>
                    </div>
                </div>
                <a href="{{ session('whatsapp_blocker_url') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition shrink-0">
                    <x-icon.whatsapp class="w-4 h-4 fill-current" />
                    <span>Launch WhatsApp Alert</span>
                </a>
            </div>
            @endif

            {{-- ══ Tasks: one checklist grouped by phase. Tick to finish, add in place, click a task for its details.
                 The board stays available from the ··· menu. ══ --}}
            @php
                $me = auth()->id();
                $today = now()->startOfDay();
                $taskRows = $project->tasks->sortBy([['order_column', 'asc'], ['id', 'asc']])->values();
                $phaseGroups = $taskRows->groupBy(fn ($t) => $t->phase_name ?: 'General');
                $isOpenTask = fn ($t) => $t->status !== 'completed';
                $taskFlags = $taskRows->mapWithKeys(fn ($t) => [$t->id => array_values(array_filter([
                    $isOpenTask($t) && (int) $t->assigned_to === (int) $me ? 'mine' : null,
                    $isOpenTask($t) && $t->due_date && $t->due_date->lt($today) ? 'overdue' : null,
                    $t->status === 'blocked' ? 'blocked' : null,
                ]))]);
                $flagCount = fn ($flag) => $taskFlags->filter(fn ($f) => in_array($flag, $f, true))->count();
                $closedPhases = $phaseGroups->values()->keys()->filter(fn ($i) => $phaseGroups->values()[$i]->every(fn ($t) => $t->status === 'completed'))->values();
                $statusWords = ['pending' => 'To do', 'in_progress' => 'Doing', 'under_review' => 'Needs checking', 'completed' => 'Done', 'blocked' => 'Blocked', 'rejected' => 'Needs a new person'];
            @endphp

            <style>
                .tk-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: var(--radius-card); }
                .tk-muted { color: var(--text-muted); }
                .tk-chip { font-size: 13px; padding: 3px 11px; border-radius: 999px; border: 1px solid var(--input-border); color: var(--text-muted); background: transparent; cursor: pointer; }
                .tk-chip.on { border-color: var(--crm-accent); color: var(--crm-accent); background: color-mix(in srgb, var(--crm-accent) 10%, transparent); }
                .tk-phase-head { display: flex; width: 100%; align-items: center; gap: 10px; padding: 10px 16px; background: var(--row-hover); border-top: 1px solid var(--table-border); font-size: 14px; text-align: left; color: var(--text-main); cursor: pointer; }
                .tk-caret { width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 5px solid currentColor; transition: transform .15s; }
                .tk-caret.shut { transform: rotate(-90deg); }
                .tk-row { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-top: 1px solid var(--table-border); font-size: 14px; }
                .tk-row:hover { background: var(--row-hover); }
                .tk-box { position: relative; flex-shrink: 0; width: 18px; height: 18px; border: 1.5px solid var(--input-border); border-radius: 4px; background: var(--card-bg); cursor: pointer; }
                .tk-box.on { background: var(--status-success, #16a34a); border-color: var(--status-success, #16a34a); }
                .tk-box.on::after { content: ''; position: absolute; left: 5px; top: 1px; width: 5px; height: 10px; border: solid #fff; border-width: 0 2px 2px 0; transform: rotate(45deg); }
                .tk-title { flex: 1; min-width: 0; text-align: left; background: transparent; border: 0; padding: 0; color: var(--text-main); font-size: 14px; cursor: pointer; }
                .tk-title:hover { text-decoration: underline; }
                .tk-row.is-done .tk-title { color: var(--text-muted); text-decoration: line-through; }
                .tk-meta { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 4px 14px; font-size: 13px; color: var(--text-muted); white-space: nowrap; }
                html body .tk-card .tk-add input.tk-add-input, html body .tk-card .tk-add input.tk-add-input:focus { flex: 1; width: 100% !important; max-width: none !important; border: 0 !important; background: transparent !important; box-shadow: none !important; outline: none !important; padding: 0 !important; height: auto !important; min-height: 0 !important; font-size: 14px !important; color: var(--text-main) !important; }
                .tk-drawer { background: var(--card-bg); border-left: 1px solid var(--card-border); }
                .tk-drawer dt { color: var(--text-muted); font-size: 13px; }
                .tk-drawer dd { font-size: 14px; color: var(--text-main); }
                @media (pointer: coarse) { .tk-box { width: 24px; height: 24px; } .tk-box.on::after { left: 8px; top: 3px; } }
            </style>

            <div class="tk-card"
                 x-data="{
                     taskFilter: 'all',
                     drawer: null,
                     busy: null,
                     moreOpen: false,
                     done: @js($taskRows->where('status', 'completed')->pluck('id')->values()),
                     total: {{ $tasksCount }},
                     closed: @js($closedPhases),
                     show(flags) { return this.taskFilter === 'all' || flags.includes(this.taskFilter); },
                     anyShown(list) { return list.some((f) => this.show(f)); },
                     isDone(id) { return this.done.includes(id); },
                     doneIn(ids) { return ids.filter((i) => this.done.includes(i)).length; },
                     togglePhase(i) { this.closed = this.closed.includes(i) ? this.closed.filter((x) => x !== i) : [...this.closed, i]; },
                     async setDone(id, makeDone, assigned) {
                         this.busy = id;
                         try {
                             const res = await fetch(`/projects/{{ $project->id }}/tasks/${id}/status`, {
                                 method: 'PATCH',
                                 headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                 body: JSON.stringify({ status: makeDone ? 'completed' : (assigned ? 'in_progress' : 'pending') })
                             });
                             const data = await res.json();
                             if (!res.ok || !data.success) throw new Error(data.message || 'failed');
                             this.done = makeDone ? [...this.done, id] : this.done.filter((x) => x !== id);
                         } catch (e) {
                             crmAlert('That task could not be updated. Try again.', 'error');
                         } finally {
                             this.busy = null;
                         }
                     },
                     async quickAdd(phase, input) {
                         const title = input.value.trim();
                         if (!title) return;
                         input.disabled = true;
                         try {
                             const res = await fetch(`/projects/{{ $project->id }}/tasks`, {
                                 method: 'POST',
                                 headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                 body: JSON.stringify({ title: title, phase_name: phase, priority: 'medium', status: 'pending' })
                             });
                             const data = await res.json();
                             if (!res.ok || !data.success) throw new Error(data.message || 'failed');
                             window.location.reload();
                         } catch (e) {
                             crmAlert('That task could not be added. Try again.', 'error');
                             input.disabled = false;
                         }
                     }
                 }"
                 @keydown.escape.window="drawer = null; moreOpen = false">

                {{-- Header: progress, one main button, the rest under ··· --}}
                <div class="flex flex-wrap items-center gap-3 px-4 py-3.5">
                    <div class="flex-1 min-w-[180px]">
                        <div class="text-[15px] font-semibold" style="color: var(--text-main)">
                            Tasks
                            @if($tasksCount > 0)<span class="font-normal tk-muted" x-text="'· ' + done.length + ' of ' + total + ' done'"></span>@endif
                        </div>
                        @if($tasksCount > 0)
                            <div class="mt-2 h-1 max-w-[280px] rounded-full overflow-hidden" style="background: var(--table-border)">
                                <div class="h-full rounded-full transition-all" style="background: var(--status-success, #16a34a)" :style="{ width: (total ? Math.round(done.length / total * 100) : 0) + '%' }"></div>
                            </div>
                        @endif
                    </div>
                    <button type="button" @click="taskModalOpen = true" class="btn-primary">Add task</button>
                    <div class="relative" @click.outside="moreOpen = false">
                        <button type="button" @click="moreOpen = !moreOpen" class="btn-secondary" style="padding: 0 10px !important" aria-label="More task actions" :aria-expanded="moreOpen">
                            <svg class="w-4 h-4 icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                        </button>
                        <div x-show="moreOpen" x-cloak class="absolute right-0 mt-1 z-30 w-52 py-1 tk-card shadow-lg text-sm">
                            <button type="button" class="block w-full h-auto text-left px-3 py-2 hover:bg-[var(--row-hover)]" @click="moreOpen = false; templateModalOpen = true">Use a template</button>
                            @if($tasksCount > 0)
                                <button type="button" class="block w-full h-auto text-left px-3 py-2 hover:bg-[var(--row-hover)]" @click="moreOpen = false; openBlockerModal(null, null)">Flag a blocker</button>
                                <button type="button" class="block w-full h-auto text-left px-3 py-2 hover:bg-[var(--row-hover)]" @click="moreOpen = false; activeTaskView = activeTaskView === 'kanban' ? 'list' : 'kanban'" x-text="activeTaskView === 'kanban' ? 'Show as a list' : 'Show as a board'"></button>
                            @endif
                        </div>
                    </div>
                </div>

                @if($tasksCount === 0)
                    {{-- Empty project: start from a ready list of steps, or add your own --}}
                    <div class="px-4 pb-6 pt-2 border-t" style="border-color: var(--table-border)">
                        <p class="text-sm mt-3" style="color: var(--text-main)">No tasks yet. Start from a ready list of steps for this kind of job, or add your own.</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach($workflowTemplates as $tKey => $tpl)
                                <button type="button" class="btn-secondary" @click="selectedTemplateKey = @js($tKey); templateModalOpen = true">{{ $tpl['name'] ?? Str::headline($tKey) }}</button>
                            @endforeach
                            <button type="button" class="btn-secondary" @click="taskModalOpen = true">Add my own task</button>
                        </div>
                    </div>
                @else
                    {{-- Quick filters --}}
                    <div class="flex flex-wrap gap-2 px-4 pb-3" x-show="activeTaskView !== 'kanban'">
                        <button type="button" class="tk-chip h-auto" :class="taskFilter === 'all' && 'on'" @click="taskFilter = 'all'">All {{ $tasksCount }}</button>
                        <button type="button" class="tk-chip h-auto" :class="taskFilter === 'mine' && 'on'" @click="taskFilter = 'mine'">Mine {{ $flagCount('mine') }}</button>
                        <button type="button" class="tk-chip h-auto" :class="taskFilter === 'overdue' && 'on'" @click="taskFilter = 'overdue'">Overdue {{ $flagCount('overdue') }}</button>
                        <button type="button" class="tk-chip h-auto {{ $blockedTasksCount > 0 ? 'text-rose-700 dark:text-rose-400' : '' }}" :class="taskFilter === 'blocked' && 'on'" @click="taskFilter = 'blocked'">Blocked {{ $blockedTasksCount }}</button>
                    </div>

                    {{-- The checklist, one section per phase --}}
                    <div x-show="activeTaskView !== 'kanban'">
                        @foreach($phaseGroups as $phaseName => $phaseTasks)
                            @php
                                $pi = $loop->index;
                                $ids = $phaseTasks->pluck('id')->values();
                                $phaseFlags = $phaseTasks->map(fn ($t) => $taskFlags[$t->id])->values();
                            @endphp
                            <div x-show="taskFilter === 'all' || anyShown(@js($phaseFlags))">
                                <button type="button" class="tk-phase-head h-auto" @click="togglePhase({{ $pi }})" :aria-expanded="!closed.includes({{ $pi }})">
                                    <span class="tk-caret" :class="closed.includes({{ $pi }}) && 'shut'" aria-hidden="true"></span>
                                    <span class="font-medium">{{ $phaseName }}</span>
                                    <span class="text-[13px] tk-muted" x-text="doneIn(@js($ids)) + ' of {{ $ids->count() }} done'"></span>
                                </button>

                                <div x-show="!closed.includes({{ $pi }})">
                                    @foreach($phaseTasks as $task)
                                        @php
                                            $overdue = $isOpenTask($task) && $task->due_date && $task->due_date->lt($today);
                                            $dueToday = $isOpenTask($task) && $task->due_date && $task->due_date->isSameDay($today);
                                            $checkTotal = $task->checklists->count();
                                            $checkDone = $task->checklists->where('is_completed', true)->count();
                                        @endphp
                                        <div class="tk-row" x-show="show(@js($taskFlags[$task->id]))" :class="isDone({{ $task->id }}) && 'is-done'">
                                            <button type="button" class="tk-box h-auto" :class="isDone({{ $task->id }}) && 'on'" :disabled="busy === {{ $task->id }}"
                                                    @click="setDone({{ $task->id }}, !isDone({{ $task->id }}), {{ $task->assigned_to ? 'true' : 'false' }})"
                                                    :aria-label="(isDone({{ $task->id }}) ? 'Mark not done: ' : 'Mark done: ') + @js($task->title)"></button>
                                            <button type="button" class="tk-title h-auto truncate" @click="drawer = {{ $task->id }}">{{ $task->title }}</button>
                                            <div class="tk-meta" x-show="!isDone({{ $task->id }})">
                                                @if($task->status === 'blocked')
                                                    <span class="text-rose-700 dark:text-rose-400 truncate max-w-[260px]" title="{{ $task->blocker_reason }}">Blocked: {{ $task->blocker_reason ?: 'waiting' }}</span>
                                                @elseif($task->status === 'under_review')
                                                    <span style="color: var(--crm-accent)">Needs checking</span>
                                                @elseif($task->status === 'rejected')
                                                    <span class="text-amber-700 dark:text-amber-400">Needs a new person</span>
                                                @endif
                                                @if(in_array($task->priority, ['high', 'urgent'], true))
                                                    <span class="text-amber-700 dark:text-amber-400">{{ ucfirst($task->priority) }}</span>
                                                @endif
                                                @if($checkTotal > 0)<span>{{ $checkDone }}/{{ $checkTotal }} checks</span>@endif
                                                <span>{{ $task->assignedUser?->name ?? 'Anyone' }}</span>
                                                @if($task->due_date)
                                                    <span class="{{ $overdue ? 'text-rose-700 dark:text-rose-400' : ($dueToday ? 'text-amber-700 dark:text-amber-400' : '') }}">{{ $overdue ? 'Overdue · ' . $task->due_date->format('d M') : ($dueToday ? 'Due today' : $task->due_date->format('d M')) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="tk-row tk-add tk-muted cursor-text" x-show="taskFilter === 'all'" @click="$el.querySelector('input').focus()">
                                        <span class="w-[18px] text-center text-base leading-none" aria-hidden="true">+</span>
                                        <input type="text" class="tk-add-input border-0 bg-transparent" style="border: 0 !important; box-shadow: none !important; background: transparent !important" placeholder="Add a task to {{ $phaseName }}" aria-label="Add a task to {{ $phaseName }}" @keydown.enter.prevent="quickAdd(@js($phaseName), $el)">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="tk-row tk-muted" x-show="taskFilter !== 'all' && !anyShown(@js($taskFlags->values()))"
                             x-text="({ mine: 'No open tasks for you.', overdue: 'Nothing is overdue.', blocked: 'Nothing is blocked.' })[taskFilter]"></div>
                        <div class="tk-row tk-muted text-[13px]" x-show="taskFilter === 'all'">
                            <button type="button" class="h-auto underline" @click="taskModalOpen = true">Add a task in a new phase</button>
                        </div>
                    </div>

                    {{-- Details panel for each task --}}
                    @foreach($taskRows as $task)
                        @php
                            $pmPhone = preg_replace('/[^0-9]/', '', (string) ($project->leadTechnician?->phone ?? $project->assignedUser?->phone ?? ''));
                            if (strlen($pmPhone) === 10) { $pmPhone = '91' . $pmPhone; }
                            $waLink = $task->status === 'blocked' && $pmPhone !== ''
                                ? 'https://wa.me/' . $pmPhone . '?text=' . rawurlencode("Task blocked on {$project->title} ({$project->project_code})\nTask: {$task->title}\nPhase: {$task->phase_name}\nReason: " . ($task->blocker_reason ?? 'waiting'))
                                : null;
                        @endphp
                        <div x-show="drawer === {{ $task->id }}" x-cloak class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="Task details">
                            <div class="absolute inset-0 bg-slate-900/40" @click="drawer = null"></div>
                            <div class="tk-drawer relative h-full w-full max-w-md overflow-y-auto">
                                <div class="flex items-start justify-between gap-3 px-5 py-4 border-b" style="border-color: var(--table-border)">
                                    <div class="min-w-0">
                                        <div class="text-xs tk-muted">{{ $task->phase_name ?: 'General' }}</div>
                                        <h3 class="text-base font-semibold mt-0.5" style="color: var(--text-main)">{{ $task->title }}</h3>
                                    </div>
                                    <x-close-button @click="drawer = null" />
                                </div>

                                <div class="px-5 py-4 space-y-5">
                                    <div>
                                        <label class="block text-xs tk-muted mb-1" for="tk-status-{{ $task->id }}">Status</label>
                                        @if($task->status === 'blocked')
                                            <div class="p-3 rounded border text-sm" style="border-color: var(--card-border)">
                                                <div class="text-rose-700 dark:text-rose-400 font-medium">Blocked</div>
                                                <div class="mt-0.5" style="color: var(--text-main)">{{ $task->blocker_reason ?: 'Waiting' }}</div>
                                                <div class="text-xs tk-muted mt-1">by {{ $task->blockedByUser?->name ?? 'someone' }} · {{ $task->blocked_at?->diffForHumans() ?? 'recently' }}</div>
                                                <div class="flex flex-wrap gap-2 mt-3">
                                                    <button type="button" class="btn-primary" @click="unblockTask({{ $task->id }})">Resolve</button>
                                                    @if($waLink)
                                                        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn-secondary"><x-icon.whatsapp class="w-4 h-4" /> WhatsApp the manager</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex flex-wrap items-center gap-2">
                                                <select id="tk-status-{{ $task->id }}" class="max-w-[200px]" data-task-title="{{ $task->title }}" @change="updateTaskStatus({{ $task->id }}, $el)">
                                                    @foreach(['pending', 'in_progress', 'under_review', 'completed'] as $st)
                                                        <option value="{{ $st }}" @selected($task->status === $st)>{{ $statusWords[$st] }}</option>
                                                    @endforeach
                                                    @if($task->status === 'rejected')<option value="rejected" selected disabled>{{ $statusWords['rejected'] }}</option>@endif
                                                </select>
                                                <button type="button" class="btn-secondary" @click="drawer = null; openBlockerModal({{ $task->id }}, @js($task->title))">Flag a blocker</button>
                                            </div>
                                        @endif
                                    </div>

                                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                                        <div><dt>Assigned to</dt><dd>{{ $task->assignedUser?->name ?? 'Anyone' }}</dd></div>
                                        <div><dt>Priority</dt><dd>{{ ucfirst($task->priority ?? 'medium') }}</dd></div>
                                        <div><dt>Start</dt><dd>{{ $task->start_date?->format('d M Y') ?? '—' }}</dd></div>
                                        <div><dt>Due</dt><dd>{{ $task->due_date?->format('d M Y') ?? '—' }}</dd></div>
                                        @if($task->estimated_hours || $task->actual_hours)
                                            <div><dt>Hours</dt><dd>{{ $task->actual_hours ? ($task->actual_hours + 0) . ' of ' : '' }}{{ ($task->estimated_hours ?? 0) + 0 }} planned</dd></div>
                                        @endif
                                        @if($task->shift)<div><dt>Shift</dt><dd>{{ $task->shift }}</dd></div>@endif
                                        @if($task->location)<div class="col-span-2"><dt>Location</dt><dd>{{ $task->location }}</dd></div>@endif
                                        @if($task->dependsOnTask)<div class="col-span-2"><dt>Starts after</dt><dd>{{ $task->dependsOnTask->title }}</dd></div>@endif
                                        @if($task->completed_at)<div class="col-span-2"><dt>Finished</dt><dd>{{ $task->completed_at->format('d M Y, h:i A') }} by {{ $task->completedByUser?->name ?? 'someone' }}</dd></div>@endif
                                    </dl>

                                    @if($task->description)
                                        <div>
                                            <div class="text-xs tk-muted mb-1">Notes</div>
                                            <p class="text-sm whitespace-pre-line" style="color: var(--text-main)">{{ $task->description }}</p>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="text-xs tk-muted mb-1.5">Checklist</div>
                                        @foreach($task->checklists as $item)
                                            <label class="flex items-start gap-2 py-1 text-sm cursor-pointer" x-data="{ on: {{ $item->is_completed ? 'true' : 'false' }} }">
                                                <input type="checkbox" class="mt-0.5" :checked="on" @change="on = !on; toggleChecklist({{ $item->id }}, $el)">
                                                <span :class="on && 'line-through tk-muted'" style="color: var(--text-main)">{{ $item->item_text }}</span>
                                            </label>
                                        @endforeach
                                        <form method="POST" action="{{ route('tasks.checklists.store', $task) }}" class="flex gap-2 mt-2">
                                            @csrf
                                            <input type="text" name="item_text" required placeholder="Add a check" aria-label="Add a check to {{ $task->title }}" class="flex-1 min-w-0">
                                            <button type="submit" class="btn-secondary btn-submit">Add</button>
                                        </form>
                                    </div>

                                    @if($task->materialConsumptions->isNotEmpty())
                                        <div>
                                            <div class="text-xs tk-muted mb-1.5">Materials used</div>
                                            <ul class="text-sm space-y-1" style="color: var(--text-main)">
                                                @foreach($task->materialConsumptions as $use)
                                                    <li class="flex justify-between gap-3"><span>{{ $use->item_name }}</span><span class="tk-muted whitespace-nowrap">{{ $use->quantity_used + 0 }} {{ $use->unit }}</span></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2 px-5 py-4 border-t" style="border-color: var(--table-border)">
                                    <button type="button" class="btn-secondary" @click="drawer = null; openEditTaskModal(@js($task->only(['id', 'title', 'phase_name', 'description', 'priority', 'status', 'assigned_to', 'estimated_hours', 'depends_on_task_id', 'blocker_reason']) + ['start_date' => $task->start_date?->toDateString(), 'due_date' => $task->due_date?->toDateString()]))">Edit details</button>
                                    <button type="button" class="btn-secondary" @click="drawer = null; openConsumeModal({{ $task->id }}, @js($task->title))">Log material used</button>
                                    @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('projects.tasks.destroy', ['project' => $project, 'task' => $task]) }}" class="ml-auto" data-confirm="Delete the task {{ $task->title }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary btn-cancel" style="color: #b91c1c !important">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            @if($tasksCount > 0)
            <div class="space-y-5">

                {{-- ════════════════════════════════════════════════════════════ --}}
                {{-- VIEW 1: VISUAL KANBAN BOARD                                  --}}
                {{-- ════════════════════════════════════════════════════════════ --}}
                <div x-show="activeTaskView === 'kanban'" class="space-y-4">
                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 px-1">
                        <span>Showing {{ $tasksCount }} tasks</span>
                    </div>

                    {{-- 5 Columns Flex Row (Backlog -> In Progress -> Under Review -> Completed -> Blocked) - No overlap --}}
                    <div class="flex gap-4 items-start overflow-x-auto pb-4 scrollbar-thin">
                        @php
                            $columnsList = [
                                'pending' => [
                                    'title' => 'Backlog / Pending',
                                    'status' => 'pending',
                                    'border_top' => 'border-t-slate-400',
                                    'badge' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                    'bg' => 'bg-slate-50/70 dark:bg-slate-900/40',
                                    'dot' => 'bg-slate-400',
                                ],
                                'in_progress' => [
                                    'title' => 'Assigned / In Progress',
                                    'status' => 'in_progress',
                                    'border_top' => 'border-t-blue-500',
                                    'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300',
                                    'bg' => 'bg-blue-50/40 dark:bg-blue-950/20',
                                    'dot' => 'bg-blue-500',
                                ],
                                'under_review' => [
                                    'title' => 'Under Review / QA',
                                    'status' => 'under_review',
                                    'border_top' => 'border-t-purple-500',
                                    'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300',
                                    'bg' => 'bg-purple-50/40 dark:bg-purple-950/20',
                                    'dot' => 'bg-purple-500',
                                ],
                                'completed' => [
                                    'title' => 'Completed',
                                    'status' => 'completed',
                                    'border_top' => 'border-t-emerald-500',
                                    'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300',
                                    'bg' => 'bg-emerald-50/40 dark:bg-emerald-950/20',
                                    'dot' => 'bg-emerald-500',
                                ],
                                'blocked' => [
                                    'title' => 'Blocked',
                                    'status' => 'blocked',
                                    'border_top' => 'border-t-rose-500',
                                    'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300',
                                    'bg' => 'bg-rose-50/40 dark:bg-rose-950/20',
                                    'dot' => 'bg-rose-500',
                                ],
                            ];
                        @endphp

                        @foreach($columnsList as $colKey => $colDef)
                        @php
                            $colTasks = $project->tasks->where('status', $colKey)->sortBy('order_column');
                        @endphp
                        <div class="rounded-2xl border-t-4 {{ $colDef['border_top'] }} bg-white dark:bg-[#0f172a] border-x border-b border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col w-[285px] min-w-[285px] shrink-0">
                            {{-- Column Header --}}
                            <div class="p-3.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $colDef['dot'] }}"></span>
                                    <h4 class="font-extrabold text-xs text-slate-900 dark:text-white uppercase tracking-wider">
                                        {{ $colDef['title'] }}
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $colDef['badge'] }}">
                                        {{ $colTasks->count() }}
                                    </span>
                                </div>

                                <button type="button" @click="taskModalOpen = true" title="Add task to {{ $colDef['title'] }}" class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-xs font-bold transition">
                                    +
                                </button>
                            </div>

                            {{-- Dropzone / Task List --}}
                            <div class="p-2.5 space-y-3 min-h-[360px] kanban-sortable-list {{ $colDef['bg'] }}"
                                 data-status="{{ $colKey }}">
                                @foreach($colTasks as $task)
                                @php
                                    $chkProg = $task->checklist_progress;
                                @endphp
                                <div class="p-4 rounded-xl bg-white dark:bg-[#0b1329] border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md transition-all space-y-3 group cursor-grab"
                                     data-task-id="{{ $task->id }}"
                                     data-task-title="{{ $task->title }}"
                                     data-phase-md5="{{ md5($task->phase_name) }}"
                                     x-show="(activePhaseFilter === 'all' || activePhaseFilter === '{{ md5($task->phase_name) }}') && (!taskSearchQuery || '{{ strtolower(addslashes($task->title . ' ' . ($task->assignedUser?->name ?? ''))) }}'.includes(taskSearchQuery.toLowerCase()))">

                                    {{-- Card Top Row: Priority Pill, Phase, & Drag Handle --}}
                                    <div class="flex items-center justify-between gap-1.5">
                                        {{-- Priority Pill matching project schema --}}
                                        <span class="px-2 py-0.5 text-[11px] font-extrabold uppercase tracking-wide rounded-md border {{ $task->priority_badge_classes }}">
                                            {{ ucfirst($task->priority) }}
                                        </span>

                                        <div class="flex items-center gap-1">
                                            <span class="text-[11px] text-slate-400 font-semibold truncate max-w-[110px]" title="{{ $task->phase_name }}">
                                                {{ Str::limit($task->phase_name, 16) }}
                                            </span>
                                            {{-- Drag Handle --}}
                                            <span class="kanban-drag-handle p-1 text-slate-300 hover:text-slate-600 dark:hover:text-slate-200 cursor-grab active:cursor-grabbing rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Task Title --}}
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white leading-snug flex items-start gap-1.5">
                                            @if($task->status === 'completed')
                                                <span class="text-emerald-500 font-extrabold shrink-0">✓</span>
                                            @endif
                                            <span class="{{ $task->status === 'completed' ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</span>
                                        </h5>
                                        @if($task->description)
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                                                {{ $task->description }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- BLOCKER BANNER (If Task is Blocked) --}}
                                    @if($task->status === 'blocked')
                                    <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/60 space-y-1.5">
                                        <div class="flex items-start gap-1.5 text-rose-700 dark:text-rose-300 font-extrabold text-[11px] uppercase tracking-wide">
                                            <span><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></span>
                                            <span>Blocked Dependency</span>
                                        </div>
                                        <p class="text-xs font-semibold text-rose-950 dark:text-rose-100 leading-snug">
                                            "{{ $task->blocker_reason ?? 'Awaiting site clearance' }}"
                                        </p>
                                        <div class="flex items-center justify-between pt-1 border-t border-rose-200/60 dark:border-rose-900/40 text-[11px] text-rose-600 dark:text-rose-400">
                                            <span>By: {{ $task->blockedByUser?->name ?? 'Technician' }}</span>
                                            <button type="button" @click.stop="unblockTask({{ $task->id }})" class="font-bold underline hover:text-rose-800">
                                                Resolve
                                            </button>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- TASK DEPENDENCY CALLOUT (If has dependency) --}}
                                    @if($task->dependsOnTask)
                                    @php
                                        $dep = $task->dependsOnTask;
                                        $depColor = match($dep->status) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300',
                                            'blocked' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300',
                                            'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300',
                                            default => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300'
                                        };
                                    @endphp
                                    <div class="p-1.5 rounded-lg border text-[11px] {{ $depColor }} flex items-center justify-between gap-1" title="Depends on: {{ $dep->title }}">
                                        <div class="flex items-center gap-1 truncate">
                                            <span class="font-bold">Depends:</span>
                                            <span class="truncate font-medium">{{ Str::limit($dep->title, 20) }}</span>
                                        </div>
                                        <span class="font-extrabold uppercase shrink-0">
                                            @if($dep->status === 'completed')
                                                ✓ Ready
                                            @elseif($dep->status === 'blocked')
                                                <svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg> Blocked
                                            @else
                                                ⏳ {{ ucfirst(str_replace('_', ' ', $dep->status)) }}
                                            @endif
                                        </span>
                                    </div>
                                    @endif

                                    {{-- Quality Assurance SOP Checklist Mini Progress --}}
                                    @if($task->checklists->isNotEmpty())
                                    <div class="space-y-1 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                                            <span>SOP Checkpoints</span>
                                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ $chkProg['completed'] }}/{{ $chkProg['total'] }}</span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $chkProg['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Task Consumed Materials & Cable Badge --}}
                                    @if($task->materialConsumptions->isNotEmpty())
                                    <div class="p-2 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-900/60 text-[11px] space-y-1">
                                        <div class="flex items-center justify-between text-indigo-700 dark:text-indigo-300 font-extrabold">
                                            <span class="flex items-center gap-1">
                                                <span><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
                                                <span>Consumed ({{ $task->materialConsumptions->count() }})</span>
                                            </span>
                                            <button type="button" @click.stop="openConsumeModal({{ $task->id }}, '{{ addslashes($task->title) }}')" class="hover:underline text-[11px] text-indigo-600 dark:text-indigo-400 font-bold">
                                                + Log More
                                            </button>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($task->materialConsumptions->take(2) as $mUsed)
                                                <span class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-mono text-[11px] font-semibold">
                                                    {{ $qty($mUsed->quantity_used) }}{{ $mUsed->unit }} {{ Str::limit($mUsed->item_name, 14) }}
                                                </span>
                                            @endforeach
                                            @if($task->materialConsumptions->count() > 2)
                                                <span class="text-[11px] text-indigo-500 font-bold self-center">+{{ $task->materialConsumptions->count() - 2 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Card Footer: Assignee, Dates, & Quick Actions --}}
                                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                                        {{-- Assignee Avatar & Name --}}
                                        <div class="flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300 text-[11px] truncate max-w-[120px]">
                                            <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($task->assignedUser?->name ?? 'U', 0, 1)) }}
                                            </span>
                                            <span class="truncate">{{ $task->assignedUser?->name ?? 'Unassigned' }}</span>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex items-center gap-1 shrink-0">
                                            {{-- Log Material / Cable Used --}}
                                            <button type="button" @click.stop="openConsumeModal({{ $task->id }}, '{{ addslashes($task->title) }}')" class="p-1 rounded text-slate-400 hover:text-indigo-600 transition" title="Log Cable / Material Used on this task">
                                                <svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </button>

                                            @if($task->status !== 'blocked')
                                                <button type="button" @click.stop="openBlockerModal({{ $task->id }}, '{{ addslashes($task->title) }}')" class="p-1 rounded text-slate-400 hover:text-rose-600 transition" title="Flag Blocker on this task">
                                                    <svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21V4m0 0h13l-2 4 2 4H3"/></svg>
                                                </button>
                                            @endif

                                            <button type="button" @click.stop="openEditTaskModal({{ json_encode($task) }})" class="p-1 rounded text-slate-400 hover:text-blue-600 transition" title="Edit Task Details">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>

                                            @if(auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('projects.tasks.destroy', ['project' => $project, 'task' => $task]) }}" data-confirm="Delete this task?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <x-close-button size="sm" tone="danger" label="Delete task" type="submit" />
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- MODALS SECTION                                                   --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Admin Approval Modal --}}
        <div x-show="approveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="approveModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Approve &amp; Assign Executing Company</h3>
                    <x-close-button @click="approveModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.approve', $project) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="f-company-id" class="block text-xs font-bold uppercase text-slate-500 mb-1">Select Executing Company *</label>
                        <select id="f-company-id" name="company_id" required class="crm-select w-full font-bold">
                            <option value="">-- Select Executing Company --</option>
                            @forelse($companies as $comp)
                                <option value="{{ $comp->id }}" {{ (old('company_id', $project->company_id) == $comp->id) ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @empty
                                <option value="1">Precision IT Systems</option>
                                <option value="2">NP Solutions</option>
                                <option value="3">Linepix</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label for="f-per-metre-rate" class="block text-xs font-bold uppercase text-slate-500 mb-1">Per-Metre Cabling Rate (₹/m) *</label>
                        <input id="f-per-metre-rate" type="number" step="0.5" name="per_metre_rate" value="{{ old('per_metre_rate', $project->per_metre_rate ?? 8.0) }}" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                    </div>

                    <div>
                        <label for="f-approved-value" class="block text-xs font-bold uppercase text-slate-500 mb-1">Approved Project Value (₹)</label>
                        <input id="f-approved-value" type="number" step="0.5" name="approved_value" value="{{ old('approved_value', $project->budget ?? $project->approved_value ?? 0) }}" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                    </div>

                    <div>
                        <label for="f-po-number" class="block text-xs font-bold uppercase text-slate-500 mb-1">Customer PO Number</label>
                        <input id="f-po-number" type="text" name="po_number" value="{{ old('po_number', $project->po_number) }}" placeholder="e.g. PO-892" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-mono">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="approveModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Confirm Approval</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Admin Rejection Modal --}}
        <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="rejectModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Reject Project</h3>
                    <x-close-button @click="rejectModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.reject', $project) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="f-rejection-reason" class="block text-xs font-bold uppercase text-slate-500 mb-1">Rejection Reason *</label>
                        <textarea id="f-rejection-reason" name="rejection_reason" rows="3" required placeholder="Specify why this project cannot be approved..." class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="rejectModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-md">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add Material Modal --}}
        <div x-show="materialModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="materialModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Add Material Line Item to BOM</h3>
                    <x-close-button @click="materialModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.materials.store', $project) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="f-product-id" class="block text-xs font-bold uppercase text-slate-500 mb-1">Select from Catalog (Optional)</label>
                        <select id="f-product-id" name="product_id" onchange="if(this.value) { const opt = this.selectedOptions[0]; document.getElementById('bom_item_name').value = opt.dataset.name; document.getElementById('bom_unit').value = opt.dataset.unit || 'nos'; }" class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            <option value="">-- Custom Non-Catalog Item --</option>
                            @foreach($availableProducts as $prod)
                                <option value="{{ $prod->id }}" data-name="{{ $prod->name }}" data-unit="{{ $prod->unit }}">
                                    [{{ $prod->sku }}] {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="bom_item_name" class="block text-xs font-bold uppercase text-slate-500 mb-1">Item Description *</label>
                        <input type="text" id="bom_item_name" name="item_name" required placeholder="e.g. 4-Channel SMPS Power Supply or Cat6 Cable" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-medium">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-quantity" class="block text-xs font-bold uppercase text-slate-500 mb-1">Quantity *</label>
                            <input id="f-quantity" type="number" step="any" min="0.01" name="quantity" required placeholder="1" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold font-mono text-blue-600 dark:text-blue-400">
                        </div>
                        <div>
                            <label for="bom_unit" class="block text-xs font-bold uppercase text-slate-500 mb-1">Unit *</label>
                            <select id="bom_unit" name="unit" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                                <option value="meters">meters (mtr)</option>
                                <option value="nos">nos (units)</option>
                                <option value="box">box</option>
                                <option value="roll">roll</option>
                                <option value="pkt">pkt</option>
                                <option value="feet">feet</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-unit-cost" class="block text-xs font-bold uppercase text-slate-500 mb-1">Est. Unit Cost (₹)</label>
                            <input id="f-unit-cost" type="number" step="any" min="0" name="unit_cost" placeholder="0.00" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-mono">
                        </div>
                        <div>
                            <label for="f-source" class="block text-xs font-bold uppercase text-slate-500 mb-1">Sourcing Channel *</label>
                            <select id="f-source" name="source" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                <option value="warehouse">Warehouse Dispatch</option>
                                <option value="local_purchase">Direct Local Purchase</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="f-shop-name" class="block text-xs font-bold uppercase text-slate-500 mb-1">Shop Name (if local purchase)</label>
                        <input id="f-shop-name" type="text" name="shop_name" placeholder="Vendor / Hardware shop name" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div>
                        <label for="f-notes" class="block text-xs font-bold uppercase text-slate-500 mb-1">Notes / Location Tag</label>
                        <input id="f-notes" type="text" name="notes" placeholder="e.g. For Floor 1 perimeter cameras" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="materialModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Add to BOM</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Log Material & Cable Consumption Modal --}}
        <div x-show="consumeModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
            <div @click.away="consumeModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col max-h-[90vh] overflow-hidden my-auto">
                <div class="px-6 pt-5 pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                            <span><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
                            <span>Log Material / Cable Used</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Record actual meters laid or hardware installed for a specific task</p>
                    </div>
                    <x-close-button @click="consumeModalOpen = false" />
                </div>

                {{-- Two-Step Navigation Tabs --}}
                <div class="px-6 py-2.5 bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2 shrink-0">
                    <button type="button" @click="consumeStep = 1" 
                            class="flex-1 flex items-center justify-center gap-2 py-1.5 px-3 rounded-xl text-xs font-semibold transition border cursor-pointer"
                            :class="consumeStep === 1 
                                ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-700 shadow-xs ring-1 ring-blue-500/20' 
                                : 'text-slate-500 dark:text-slate-400 border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold"
                              :class="consumeStep === 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                            1
                        </span>
                        <span class="truncate">Step 1: Task & Material</span>
                    </button>

                    <div class="w-3 h-px bg-slate-200 dark:bg-slate-700 shrink-0"></div>

                    <button type="button" @click="nextConsumeStep()" 
                            class="flex-1 flex items-center justify-center gap-2 py-1.5 px-3 rounded-xl text-xs font-semibold transition border cursor-pointer"
                            :class="consumeStep === 2 
                                ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-700 shadow-xs ring-1 ring-blue-500/20' 
                                : 'text-slate-500 dark:text-slate-400 border-transparent hover:bg-slate-100 dark:hover:bg-slate-800/60'">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold"
                              :class="consumeStep === 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                            2
                        </span>
                        <span class="truncate">Step 2: Notes & Stock</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('projects.material-consumptions.store', $project) }}" class="flex flex-col flex-1 overflow-hidden m-0">
                    @csrf
                    <input type="hidden" name="redirect_tab" :value="currentTab === 'tasks' ? 'tasks' : 'materials'">

                    <div class="p-6 overflow-y-auto space-y-4 flex-1">
                        {{-- Step 1 Error Alert --}}
                        <div x-show="consumeStep1Error" x-text="consumeStep1Error" class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-medium"></div>

                        {{-- STEP 1: Task & Material Information --}}
                        <div x-show="consumeStep === 1" class="space-y-4">
                            {{-- Target Task --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Associated Project Task *</label>
                                    <button type="button" 
                                            @click="consumeModalOpen = false; taskModalOpen = true" 
                                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                        + Add New Task
                                    </button>
                                </div>

                                @if($project->tasks->isEmpty())
                                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs space-y-2">
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>No project tasks exist yet!</span>
                                        </div>
                                        <p class="text-[11px] leading-relaxed">
                                            Materials and cable meters must be allocated against a specific project milestone or task.
                                        </p>
                                        <div class="flex items-center gap-2 pt-1">
                                            <button type="button" 
                                                    @click="consumeModalOpen = false; taskModalOpen = true" 
                                                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition inline-flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span>Create First Task</span>
                                            </button>
                                            <button type="button" 
                                                    @click="consumeModalOpen = false; templateModalOpen = true" 
                                                    class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer">
                                                Apply Workflow Template
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <select aria-label="Task the material was used for" name="project_task_id" x-model="consumeTaskId" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl outline-none font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        <option value="">-- Select Project Task --</option>
                                        @foreach($project->tasks as $tItem)
                                            <option value="{{ $tItem->id }}">
                                                [{{ $tItem->phase_name }}] {{ $tItem->title }} ({{ ucfirst($tItem->status) }})
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            {{-- BOM Item Link (Optional) --}}
                            @if($project->materials->isNotEmpty())
                            <div>
                                <label for="f-project-material-id" class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Link to Planned BOM Item (Optional)</label>
                                <select id="f-project-material-id" name="project_material_id" x-model="consumeMaterialId" @change="onBomSelectChange($event)" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                    <option value="" data-item-name="" data-unit="meters" data-product-id="">-- Direct Entry / Unplanned Item --</option>
                                    @foreach($project->materials as $bItem)
                                        <option value="{{ $bItem->id }}" data-item-name="{{ $bItem->item_name }}" data-unit="{{ $bItem->unit }}" data-product-id="{{ $bItem->product_id }}">
                                            {{ $bItem->item_name }} (Allocated: {{ $bItem->quantity }} {{ $bItem->unit }} &bull; Remaining: {{ $bItem->remaining_quantity }} {{ $bItem->unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            {{-- Item Description --}}
                            <div>
                                <label for="f-item-name" class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Material / Cable Description *</label>
                                <input id="f-item-name" type="text" name="item_name" x-model="consumeItemName" placeholder="e.g. D-Link Cat6 UTP Solid Cable" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-medium">
                            </div>

                            {{-- Quantity and Unit --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="f-quantity-used" class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Quantity Used *</label>
                                    <input id="f-quantity-used" type="number" step="any" min="0.01" name="quantity_used" x-model="consumeQty" placeholder="e.g. 125" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold font-mono text-indigo-600 dark:text-indigo-400">
                                </div>
                                <div>
                                    <label for="f-unit" class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Unit of Measure *</label>
                                    <select id="f-unit" name="unit" x-model="consumeUnit" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                                        <option value="meters">meters (mtr)</option>
                                        <option value="nos">nos (units)</option>
                                        <option value="rolls">rolls</option>
                                        <option value="coils">coils</option>
                                        <option value="box">box</option>
                                        <option value="pkt">pkt</option>
                                        <option value="feet">feet</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 2: Execution Notes, Reel & Warehouse Stock --}}
                        <div x-show="consumeStep === 2" class="space-y-4">
                            {{-- Execution Notes / Location (Prominent & Clear) --}}
                            <div>
                                <label for="f-notes-2" class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                                    <span>Execution Notes / Location</span>
                                    <span class="text-[11px] font-normal text-slate-400">(Where installed / cable routing)</span>
                                </label>
                                <textarea id="f-notes-2" name="notes" x-model="consumeNotes" rows="3" placeholder="e.g. Pulled from server rack on 1st floor to Reception desk and front gate camera." class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none"></textarea>
                            </div>

                            {{-- Wastage & Drum / Reel # --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="f-wastage-quantity" class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Scrap / Cut Wastage</label>
                                    <input id="f-wastage-quantity" type="number" step="any" min="0" name="wastage_quantity" x-model="consumeWastage" placeholder="0" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-mono">
                                </div>
                                <div>
                                    <label for="f-drum-or-batch-no" class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Cable Drum / Reel #</label>
                                    <input id="f-drum-or-batch-no" type="text" name="drum_or_batch_no" x-model="consumeDrumNo" placeholder="e.g. Drum #2 (140m-265m)" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-mono">
                                </div>
                            </div>

                            {{-- Warehouse Catalog Item Link & Stock Deduction --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Warehouse Inventory Integration</label>
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-600 dark:text-slate-400">
                                        <input type="checkbox" name="deduct_from_stock" value="1" x-model="consumeDeductStock" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span>Auto-deduct stock</span>
                                    </label>
                                </div>
                                <select aria-label="Product" name="product_id" x-model="consumeProductId" class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                    <option value="">-- Match Catalog Product (Optional) --</option>
                                    @foreach($availableProducts as $pItem)
                                        <option value="{{ $pItem->id }}">
                                            [{{ $pItem->sku }}] {{ $pItem->name }} (In Stock: {{ $pItem->stock_quantity }} {{ $pItem->unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Modal Footer with Step Navigation Buttons --}}
                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
                        <div x-show="consumeStep === 1" class="w-full flex items-center justify-between">
                            <button type="button" @click="consumeModalOpen = false" class="btn-secondary text-xs">
                                Cancel
                            </button>
                            <button type="button" @click="nextConsumeStep()" class="btn-primary text-xs shadow-md inline-flex items-center gap-1.5 cursor-pointer">
                                <span>Next: Notes & Stock</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div x-show="consumeStep === 2" class="w-full flex items-center justify-between">
                            <button type="button" @click="consumeStep = 1" class="btn-secondary text-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                <span>Back to Step 1</span>
                            </button>
                            <button type="submit" class="btn-primary text-xs shadow-md inline-flex items-center gap-1.5 cursor-pointer">
                                <span><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
                                <span>Record Consumption</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Upload Document / Delivery Challan Modal --}}
        <div x-show="documentModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="documentModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Upload Document / Delivery Challan</h3>
                    <x-close-button @click="documentModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.documents.store', $project) }}" enctype="multipart/form-data" class="space-y-4"
                      x-data="{ docType: 'delivery_challan' }">
                    @csrf
                    <div>
                        <label for="f-document-type" class="block text-xs font-bold uppercase text-slate-500 mb-1">Document Type *</label>
                        <select id="f-document-type" name="document_type" x-model="docType" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                            <option value="delivery_challan">Delivery Challan (DC)</option>
                            <option value="purchase_order">Customer Purchase Order (PO)</option>
                            <option value="site_drawing">Site Blueprint / Wiring Drawing</option>
                            <option value="completion_signoff">Completion Sign-off Sheet</option>
                            <option value="invoice">Customer Invoice</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>

                    <div>
                        <label for="f-title" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Document Title <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <input id="f-title" type="text" 
                               name="title" 
                               placeholder="e.g. Delivery Challan (Optional - will default to DC Number or File Name)" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <div x-show="docType === 'delivery_challan'" class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-dc-number" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                DC Number <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input id="f-dc-number" type="text" 
                                   name="dc_number" 
                                   :required="docType === 'delivery_challan'" 
                                   placeholder="DC-2026-0042" 
                                   class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl font-mono font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="f-dc-date" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">DC Date</label>
                            <input id="f-dc-date" type="date" 
                                   name="dc_date" 
                                   value="{{ now()->toDateString() }}" 
                                   class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                    </div>

                    <div x-show="docType === 'delivery_challan'">
                        <label for="f-items-summary" class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Items Summary in Challan</label>
                        <textarea id="f-items-summary" name="items_summary" rows="2" placeholder="e.g. 4x Dome Camera, 1x NVR, 150m CAT6 Cable" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                    </div>

                    <div>
                        <label for="f-file" class="block text-xs font-bold uppercase text-slate-500 mb-1">Attach File (PDF, Image, up to 50MB) *</label>
                        <input id="f-file" type="file" name="file" required class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="documentModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Upload Document</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Log Daily Work Day Modal --}}
        <div x-show="workDayModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
            <div @click.away="workDayModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col max-h-[90vh] overflow-hidden my-auto"
                 x-data="{
                     workersList: {{ $workers->toJson() }},
                     rows: [
                         { worker_id: '', attendance_type: 'full_day', daily_rate: 0, cabling_metres: 0, extra_amount: 0, extra_description: '', row_total: 0 }
                     ],
                     showNewWorkerModal: false,
                     newWorkerName: '',
                     newWorkerPhone: '',
                     newWorkerDailyRate: '750',
                     newWorkerNotes: '',
                     newWorkerSaving: false,
                     newWorkerError: '',
                     newWorkerSuccess: '',
                     targetRowIndexForNewWorker: null,
                     
                     openNewWorkerModal(targetIdx = null) {
                         this.targetRowIndexForNewWorker = targetIdx;
                         this.newWorkerName = '';
                         this.newWorkerPhone = '';
                         this.newWorkerDailyRate = '750';
                         this.newWorkerNotes = '';
                         this.newWorkerError = '';
                         this.newWorkerSuccess = '';
                         this.showNewWorkerModal = true;
                     },

                     async saveNewWorker() {
                         if (!this.newWorkerName || !this.newWorkerName.trim()) {
                             this.newWorkerError = 'Please enter the worker full name.';
                             return;
                         }
                         if (!this.newWorkerDailyRate || parseFloat(this.newWorkerDailyRate) <= 0) {
                             this.newWorkerError = 'Please enter a valid daily wage rate.';
                             return;
                         }
                         this.newWorkerSaving = true;
                         this.newWorkerError = '';
                         try {
                             const res = await fetch('{{ route('workers.store') }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                 },
                                 body: JSON.stringify({
                                     name: this.newWorkerName.trim(),
                                     phone: this.newWorkerPhone ? this.newWorkerPhone.trim() : null,
                                     daily_rate: parseFloat(this.newWorkerDailyRate),
                                     notes: this.newWorkerNotes ? this.newWorkerNotes.trim() : null,
                                     is_active: 1
                                 })
                             });
                             const data = await res.json();
                             if (!res.ok || !data.success) {
                                 throw new Error(data.message || 'Failed to save worker.');
                             }
                             
                             this.workersList.push(data.worker);
                             
                             if (this.targetRowIndexForNewWorker !== null && this.rows[this.targetRowIndexForNewWorker]) {
                                 this.rows[this.targetRowIndexForNewWorker].worker_id = data.worker.id;
                                 this.updateRate(this.rows[this.targetRowIndexForNewWorker]);
                             } else {
                                 if (this.rows.length === 1 && !this.rows[0].worker_id) {
                                     this.rows[0].worker_id = data.worker.id;
                                     this.updateRate(this.rows[0]);
                                 } else {
                                     this.rows.push({
                                         worker_id: data.worker.id,
                                         attendance_type: 'full_day',
                                         daily_rate: parseFloat(data.worker.daily_rate),
                                         cabling_metres: 0,
                                         extra_amount: 0,
                                         extra_description: '',
                                         row_total: parseFloat(data.worker.daily_rate)
                                     });
                                 }
                             }
                             
                             this.newWorkerSuccess = `Worker '${data.worker.name}' (₹${data.worker.daily_rate}/day) registered successfully!`;
                             setTimeout(() => {
                                 this.showNewWorkerModal = false;
                                 this.newWorkerSuccess = '';
                             }, 1000);
                         } catch (err) {
                             this.newWorkerError = err.message || 'Error creating worker.';
                         } finally {
                             this.newWorkerSaving = false;
                         }
                     },

                     addRow() {
                         this.rows.push({ worker_id: '', attendance_type: 'full_day', daily_rate: 0, cabling_metres: 0, extra_amount: 0, extra_description: '', row_total: 0 });
                     },
                     removeRow(i) {
                         if (this.rows.length > 1) {
                             this.rows.splice(i, 1);
                         }
                     },
                     updateRate(row) {
                         const found = this.workersList.find(w => w.id == row.worker_id);
                         if (found) {
                             row.daily_rate = parseFloat(found.daily_rate) || 0;
                             this.calcRow(row);
                         }
                     },
                     calcRow(row) {
                         const base = (row.attendance_type === 'half_day') ? (row.daily_rate / 2) : row.daily_rate;
                         const cabling = (parseFloat(row.cabling_metres) || 0) * {{ (float) ($project->per_metre_rate ?? 8.0) }};
                         const extra = parseFloat(row.extra_amount) || 0;
                         row.row_total = base + cabling + extra;
                     },
                     get totalManDays() {
                         return this.rows.reduce((sum, r) => sum + (r.worker_id ? (r.attendance_type === 'half_day' ? 0.5 : 1.0) : 0), 0);
                     },
                     get totalCablingMtrs() {
                         return this.rows.reduce((sum, r) => sum + (parseFloat(r.cabling_metres) || 0), 0);
                     },
                     get totalWagePayout() {
                         return this.rows.reduce((sum, r) => sum + (r.row_total || 0), 0);
                     }
                 }">
                
                {{-- Sticky Modal Header --}}
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-white dark:bg-[#0f172a]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Log Daily Labor Shift &amp; Wages</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Worker attendance &amp; cabling incentives (₹{{ number_format((float)($project->per_metre_rate ?? 8.0), 2) }}/m)</p>
                        </div>
                    </div>
                    <x-close-button @click="workDayModalOpen = false" />
                </div>

                {{-- Scrollable Form Body --}}
                <form method="POST" action="{{ route('projects.work-days.store', $project) }}" class="flex flex-col flex-1 overflow-hidden m-0">
                    @csrf
                    <div class="p-5 overflow-y-auto space-y-4 flex-1">
                        
                        {{-- Shift Date & General Notes (Compact) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800">
                            <div class="sm:col-span-1">
                                <label for="f-work-date" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Shift Date *</label>
                                <input id="f-work-date" type="date" name="work_date" value="{{ now()->toDateString() }}" required class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg outline-none font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="f-notes-3" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">General Shift Notes</label>
                                <input id="f-notes-3" type="text" name="notes" placeholder="e.g. Ground floor cabling &amp; camera mounting" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                        </div>

                        {{-- Quick Register New Worker Drawer --}}
                        <div x-show="showNewWorkerModal" x-transition class="p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/80 space-y-3">
                            <div class="flex items-center justify-between pb-1.5 border-b border-blue-200/60 dark:border-blue-800/60">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[11px] font-bold">+</span>
                                    <h4 class="text-xs font-bold text-blue-950 dark:text-blue-200 uppercase tracking-wide">Register New Daily Worker</h4>
                                </div>
                                <button type="button" @click="showNewWorkerModal = false" class="text-slate-400 hover:text-slate-600 text-xs font-bold">&times; Close</button>
                            </div>

                            <div x-show="newWorkerError" x-text="newWorkerError" class="p-2 rounded-lg bg-rose-100 text-rose-800 border border-rose-300 text-xs"></div>
                            <div x-show="newWorkerSuccess" x-text="newWorkerSuccess" class="p-2 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs"></div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Full Name *</label>
                                    <input aria-label="Worker name" type="text" x-model="newWorkerName" placeholder="e.g. Ramesh Kumar" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg outline-none font-semibold text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Daily Wage Rate (₹) *</label>
                                    <div class="flex items-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-hidden">
                                        <span class="px-2.5 py-1.5 text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 border-r border-slate-300 dark:border-slate-700 select-none">₹</span>
                                        <input aria-label="Daily rate" type="number" step="10" min="0" x-model="newWorkerDailyRate" placeholder="750" class="w-full px-2 py-1.5 text-xs bg-transparent outline-none font-mono font-bold text-blue-600 dark:text-blue-400">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Phone Number</label>
                                    <input aria-label="Worker phone" type="text" x-model="newWorkerPhone" placeholder="e.g. 9876543210" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg outline-none font-mono text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Role / Notes</label>
                                    <input aria-label="Worker notes" type="text" x-model="newWorkerNotes" placeholder="e.g. Cable puller" class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg outline-none text-slate-900 dark:text-white">
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" @click="showNewWorkerModal = false" class="px-2.5 py-1 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    Cancel
                                </button>
                                <button type="button" @click="saveNewWorker()" :disabled="newWorkerSaving" class="px-3 py-1 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-xs inline-flex items-center gap-1 cursor-pointer disabled:opacity-50">
                                    <span x-show="newWorkerSaving">Saving...</span>
                                    <span x-show="!newWorkerSaving">Save Worker</span>
                                </button>
                            </div>
                        </div>

                        {{-- Worker Attendance Rows Header --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between pb-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Workers on Shift</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-slate-100 dark:bg-slate-800 text-slate-500" x-text="`${workersList.length} saved`"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="openNewWorkerModal(null)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition inline-flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Add New Worker</span>
                                    </button>
                                    <button type="button" @click="addRow()" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition inline-flex items-center gap-1 cursor-pointer">
                                        <span>+ Row</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Rows Cards --}}
                            <template x-for="(r, idx) in rows" :key="idx">
                                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs space-y-2.5">
                                    {{-- Row Subheader --}}
                                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-bold flex items-center justify-center" x-text="idx + 1"></span>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="r.worker_id ? (workersList.find(w => w.id == r.worker_id)?.name || 'Worker') : `Worker #${idx + 1}`"></span>
                                            <template x-if="r.daily_rate > 0">
                                                <span class="text-[11px] font-mono font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800" x-text="`₹${r.daily_rate}/day`"></span>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <button type="button" @click="openNewWorkerModal(idx)" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                                                + New Person
                                            </button>
                                            <button type="button" @click="removeRow(idx)" class="text-rose-500 hover:text-rose-700 text-xs font-semibold cursor-pointer" x-show="rows.length > 1">
                                                &times; Remove
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Row Fields Grid --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Worker *</label>
                                            <select aria-label="Worker" :name="`attendances[${idx}][worker_id]`" x-model="r.worker_id" @change="updateRate(r)" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg outline-none font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                                <option value="">-- Select Worker --</option>
                                                <template x-for="w in workersList" :key="w.id">
                                                    <option :value="w.id" x-text="`${w.name} (₹${w.daily_rate}/d)`"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Shift Duration *</label>
                                            <select aria-label="Attendance type" :name="`attendances[${idx}][attendance_type]`" x-model="r.attendance_type" @change="calcRow(r)" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg outline-none font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                                <option value="full_day">Full Day (1.0 Shift • 100%)</option>
                                                <option value="half_day">Half Day (0.5 Shift • 50%)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                                Cabling (Metres)
                                            </label>
                                            <input aria-label="Cabling metres" type="number" step="1" min="0" :name="`attendances[${idx}][cabling_metres]`" x-model="r.cabling_metres" @input="calcRow(r)" placeholder="0" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg font-mono font-bold outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Extra Allowance</label>
                                            <div class="flex items-center rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500">
                                                <span class="px-2 py-1 text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-700/60 border-r border-slate-300 dark:border-slate-700 select-none">₹</span>
                                                <input aria-label="Extra amount" type="number" step="0.5" min="0" :name="`attendances[${idx}][extra_amount]`" x-model="r.extra_amount" @input="calcRow(r)" placeholder="0" class="w-full px-2 py-1 text-xs bg-transparent outline-none font-mono text-slate-900 dark:text-white">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Reason for Extra</label>
                                            <input aria-label="Extra description" type="text" :name="`attendances[${idx}][extra_description]`" x-model="r.extra_description" placeholder="Overtime / travel" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg outline-none text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        </div>
                                    </div>

                                    {{-- Row Subtotal Summary --}}
                                    <div class="px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                            <span>Base: ₹<span x-text="r.attendance_type === 'half_day' ? (r.daily_rate / 2).toFixed(0) : r.daily_rate"></span></span>
                                            <span x-show="parseFloat(r.cabling_metres) > 0" x-text="`+ ₹${((parseFloat(r.cabling_metres) || 0) * {{ (float) ($project->per_metre_rate ?? 8.0) }}).toFixed(0)} cable`"></span>
                                            <span x-show="parseFloat(r.extra_amount) > 0" x-text="`+ ₹${(parseFloat(r.extra_amount) || 0).toFixed(0)} extra`"></span>
                                        </div>
                                        <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            Subtotal: ₹<span x-text="r.row_total.toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Real-time Shift Summary Bar (Compact) --}}
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 grid grid-cols-4 gap-2 text-center">
                            <div>
                                <span class="text-[11px] font-bold uppercase text-slate-400 block">Workers</span>
                                <span class="text-sm font-bold font-mono text-slate-800 dark:text-slate-200" x-text="rows.filter(r => r.worker_id).length"></span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase text-slate-400 block">Man-Days</span>
                                <span class="text-sm font-bold font-mono text-slate-800 dark:text-slate-200" x-text="totalManDays.toFixed(1)"></span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase text-slate-400 block">Cabling</span>
                                <span class="text-sm font-bold font-mono text-slate-800 dark:text-slate-200" x-text="`${totalCablingMtrs}m`"></span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase text-emerald-600 dark:text-emerald-400 block">Total Wages</span>
                                <span class="text-sm font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="`₹${totalWagePayout.toFixed(2)}`"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Modal Footer --}}
                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
                        <button type="button" @click="workDayModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md inline-flex items-center gap-1.5 cursor-pointer">
                            <span>Save Work Day Log</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add Single IP Device Modal --}}
        <div x-show="deviceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="deviceModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Add IP Device</h3>
                    <x-close-button @click="deviceModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.devices.store', $project) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="f-device-name" class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Name *</label>
                        <input id="f-device-name" type="text" name="device_name" required placeholder="e.g. CAM-01 Front Gate" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-ip-address" class="block text-xs font-bold uppercase text-slate-500 mb-1">IP Address *</label>
                            <input id="f-ip-address" type="text" name="ip_address" required placeholder="192.168.1.101" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label for="f-device-type" class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Type</label>
                            <input id="f-device-type" type="text" name="device_type" placeholder="Dome Camera" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-web-port" class="block text-xs font-bold uppercase text-slate-500 mb-1">Web Port</label>
                            <input id="f-web-port" type="number" name="web_port" value="80" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                        <div>
                            <label for="f-rtsp-port" class="block text-xs font-bold uppercase text-slate-500 mb-1">RTSP Port</label>
                            <input id="f-rtsp-port" type="number" name="rtsp_port" value="554" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-username" class="block text-xs font-bold uppercase text-slate-500 mb-1">Username</label>
                            <input id="f-username" type="text" name="username" value="admin" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                        <div>
                            <label for="f-password" class="block text-xs font-bold uppercase text-slate-500 mb-1">Password (Encrypted)</label>
                            <input id="f-password" type="password" name="password" placeholder="••••••••" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="deviceModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Save Device</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk Sequential Generator Modal --}}
        <div x-show="bulkDeviceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="bulkDeviceModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Bulk Sequential IP Generator</h3>
                        <p class="text-xs text-slate-400">Generate multiple sequential camera IP rows in 1-click</p>
                    </div>
                    <x-close-button @click="bulkDeviceModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.devices.bulk', $project) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label for="f-start-ip" class="block text-xs font-bold uppercase text-slate-500 mb-1">Start IP Address *</label>
                        <input id="f-start-ip" type="text" name="start_ip" value="192.168.1.101" required placeholder="192.168.1.101" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-count" class="block text-xs font-bold uppercase text-slate-500 mb-1">Total Devices Count *</label>
                            <input id="f-count" type="number" min="1" max="100" name="count" value="16" required class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label for="f-name-prefix" class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Prefix</label>
                            <input id="f-name-prefix" type="text" name="name_prefix" value="Camera" required placeholder="Camera" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold">
                        </div>
                    </div>

                    <div>
                        <label for="f-device-type-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Type</label>
                        <input id="f-device-type-2" type="text" name="device_type" value="IP Camera" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-username-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Default Username</label>
                            <input id="f-username-2" type="text" name="username" value="admin" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                        <div>
                            <label for="f-password-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Default Password</label>
                            <input id="f-password-2" type="password" name="password" placeholder="Pass123" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="bulkDeviceModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Generate All Rows</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Workflow Template Selection Modal --}}
        <div x-show="templateModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="templateModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm"><svg class="w-5 h-5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></span>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Apply Pre-Built Workflow Template</h3>
                    </div>
                    <x-close-button @click="templateModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.tasks.apply-template', $project) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="f-template-key" class="block text-xs font-bold uppercase text-slate-500 mb-1">Select Industry Template *</label>
                        <select id="f-template-key" name="template_key" required x-model="selectedTemplateKey" class="w-full px-3.5 py-2.5 text-sm font-semibold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($allWorkflowTemplates as $k => $t)
                                <option value="{{ $k }}" {{ $k === array_key_first($workflowTemplates) ? 'selected' : '' }}>
                                    {{ $t['name'] }} ({{ count($t['phases']) }} Phases &bull; ~{{ $t['estimated_days'] }} Days)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="f-assigned-to" class="block text-xs font-bold uppercase text-slate-500 mb-1">Assign All Tasks By Default To</label>
                        <select id="f-assigned-to" name="assigned_to" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            <option value="">Lead Technician ({{ $project->leadTechnician?->name ?? 'Project Assignee' }})</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-300 space-y-1">
                        <div class="font-bold">What will happen?</div>
                        <p class="text-[11px] leading-relaxed text-blue-700 dark:text-blue-400">
                            The system will automatically generate phase-wise milestones, technical requirements, estimated hours, and SOP quality assurance checklists without deleting any of your existing tasks.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="templateModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Generate Workflow</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add Custom Task Modal --}}
        <div x-show="taskModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="taskModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Add Custom Project Task</h3>
                    <x-close-button @click="taskModalOpen = false" />
                </div>

                <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label for="f-phase-name" class="block text-xs font-bold uppercase text-slate-500 mb-1">Phase / Milestone Name *</label>
                        <input id="f-phase-name" type="text" name="phase_name" list="existing_phases" required placeholder="e.g. Phase 2: Cabling &amp; Tagging" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                        <datalist id="existing_phases">
                            @foreach($tasksByPhase->keys() as $pName)
                                <option value="{{ $pName }}">
                            @endforeach
                            <option value="Phase 1: Civil &amp; Site Preparation">
                            <option value="Phase 2: Cabling &amp; Tagging">
                            <option value="Phase 3: Hardware Mounting">
                            <option value="Phase 4: IP Allocation &amp; Commissioning">
                            <option value="Phase 5: Client Sign-off &amp; JCR">
                        </datalist>
                    </div>

                    <div>
                        <label for="f-title-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Task Title *</label>
                        <input id="f-title-2" type="text" name="title" required placeholder="e.g. Pull Cat6 drop to Admin Block Entrance" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div>
                        <label for="f-description" class="block text-xs font-bold uppercase text-slate-500 mb-1">Description</label>
                        <textarea id="f-description" name="description" rows="2" placeholder="Technical instructions or notes for technician..." class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-priority" class="block text-xs font-bold uppercase text-slate-500 mb-1">Priority *</label>
                            <select id="f-priority" name="priority" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label for="f-status" class="block text-xs font-bold uppercase text-slate-500 mb-1">Status *</label>
                            <select id="f-status" name="status" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                                <option value="pending" selected>Backlog / Pending</option>
                                <option value="in_progress">Assigned / In Progress</option>
                                <option value="under_review">Under Review / QA</option>
                                <option value="completed">Completed</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-start-date" class="block text-xs font-bold uppercase text-slate-500 mb-1">Start Date</label>
                            <input id="f-start-date" type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label for="f-due-date" class="block text-xs font-bold uppercase text-slate-500 mb-1">Due Date</label>
                            <input id="f-due-date" type="date" name="due_date" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="f-depends-on-task-id" class="block text-xs font-bold uppercase text-slate-500 mb-1">Prerequisite Task Dependency</label>
                        <select id="f-depends-on-task-id" name="depends_on_task_id" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            <option value="">No Prerequisite (Can Start Immediately)</option>
                            @foreach($project->tasks as $otherTask)
                                <option value="{{ $otherTask->id }}">
                                    [{{ $otherTask->phase_name }}] {{ $otherTask->title }} ({{ ucfirst(str_replace('_', ' ', $otherTask->status)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-assigned-to-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Assigned Technician</label>
                            <select id="f-assigned-to-2" name="assigned_to" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                <option value="">Select Technician</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ $project->lead_technician_id == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="f-estimated-hours" class="block text-xs font-bold uppercase text-slate-500 mb-1">Estimated Hours</label>
                            <input id="f-estimated-hours" type="number" step="0.5" min="0" name="estimated_hours" placeholder="4.0" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="f-blocker-reason" class="block text-xs font-bold uppercase text-slate-500 mb-1">Blocker Reason (If Blocked)</label>
                        <input id="f-blocker-reason" type="text" name="blocker_reason" placeholder="e.g. Cannot start Camera Mounting until Client Finishes False Ceiling Paint" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <label for="f-checklist-items" class="block text-xs font-bold uppercase text-slate-500">Initial SOP Quality Checkpoints</label>
                        <input id="f-checklist-items" type="text" name="checklist_items[]" placeholder="Checkpoint 1 (e.g. Conduit properly clamped)" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                        <input aria-label="Checkpoint 2" type="text" name="checklist_items[]" placeholder="Checkpoint 2 (e.g. Waterproof junction box installed)" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                        <input aria-label="Checkpoint 3" type="text" name="checklist_items[]" placeholder="Checkpoint 3 (e.g. Cable ferrule marked at both ends)" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="taskModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Create Task</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Dedicated Flag Blocker Modal (Auto-Alerts WhatsApp & Dashboard Bell) --}}
        <div x-show="blockerModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="blockerModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border-2 border-rose-300 dark:border-rose-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-rose-100 dark:border-rose-900/60">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-base shadow-xs"><svg class="w-5 h-5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></span>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Flag Blocker on Task</h3>
                            <p class="text-[11px] text-slate-400">Instantly alerts Project Managers via Dashboard Notification &amp; WhatsApp</p>
                        </div>
                    </div>
                    <x-close-button @click="blockerModalOpen = false" />
                </div>

                {{-- Task Context --}}
                <div class="p-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-900/50">
                    <span class="text-[11px] font-extrabold uppercase tracking-wide text-rose-600 block">Affected Task</span>
                    <h5 class="text-xs font-extrabold text-slate-900 dark:text-white mt-0.5" x-text="blockerTaskTitle"></h5>
                </div>

                {{-- Reason Input & Quick Chips --}}
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">
                            Blocker Reason / Bottleneck Details *
                        </label>
                        <textarea aria-label="What is blocking this task" x-model="blockerReason" rows="3" required placeholder="e.g., Cannot start Camera Mounting until Client Finishes False Ceiling Paint" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-rose-500 font-medium"></textarea>
                    </div>

                    {{-- Quick Select Preset Reasons --}}
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-bold uppercase text-slate-400 block">Common CCTV Installation Blockers (Click to Fill):</span>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="chip in quickBlockers" :key="chip">
                                <button type="button" @click="setBlockerReason(chip)" class="text-[11px] px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-700 dark:bg-slate-800 dark:hover:bg-rose-950/50 dark:hover:text-rose-300 border border-slate-200 dark:border-slate-700 transition text-left">
                                    <span x-text="chip"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Auto-Alert Channels Callout --}}
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-[11px] text-slate-600 dark:text-slate-300 space-y-1">
                        <div class="font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span class="text-emerald-500">✓</span>
                            <span>Automated Multi-Channel Dispatch Enabled</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Upon submitting, a high-priority alert is saved to the topbar notification bell for Project Managers and Admins, and a formatted WhatsApp dispatch message is generated automatically.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="blockerModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                    <button type="button" @click="submitBlockerModal()" :disabled="blockerSubmitting" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-md transition flex items-center gap-1.5">
                        <span x-show="!blockerSubmitting">Confirm Blocker &amp; Alert Management</span>
                        <span x-show="blockerSubmitting">Dispatching Alerts...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Edit Task Details Modal --}}
        <div x-show="editTaskModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="editTaskModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Edit Project Task</h3>
                    <x-close-button @click="editTaskModalOpen = false" />
                </div>

                <form :action="`/projects/{{ $project->id }}/tasks/${editTaskId}`" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="f-phase-name-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Phase / Milestone Name *</label>
                        <input id="f-phase-name-2" type="text" name="phase_name" x-model="editTaskPhase" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                    </div>

                    <div>
                        <label for="f-title-3" class="block text-xs font-bold uppercase text-slate-500 mb-1">Task Title *</label>
                        <input id="f-title-3" type="text" name="title" x-model="editTaskTitle" required class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold">
                    </div>

                    <div>
                        <label for="f-description-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Description</label>
                        <textarea id="f-description-2" name="description" x-model="editTaskDescription" rows="2" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-priority-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Priority *</label>
                            <select id="f-priority-2" name="priority" x-model="editTaskPriority" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label for="f-status-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Status *</label>
                            <select id="f-status-2" name="status" x-model="editTaskStatus" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-semibold">
                                <option value="pending">Backlog / Pending</option>
                                <option value="in_progress">Assigned / In Progress</option>
                                <option value="under_review">Under Review / QA</option>
                                <option value="completed">Completed</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-start-date-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Start Date</label>
                            <input id="f-start-date-2" type="date" name="start_date" x-model="editTaskStartDate" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label for="f-due-date-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Due Date</label>
                            <input id="f-due-date-2" type="date" name="due_date" x-model="editTaskDueDate" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="f-depends-on-task-id-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Prerequisite Task Dependency</label>
                        <select id="f-depends-on-task-id-2" name="depends_on_task_id" x-model="editTaskDependsOn" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            <option value="">No Prerequisite (Can Start Immediately)</option>
                            @foreach($project->tasks as $otherTask)
                                <option value="{{ $otherTask->id }}">
                                    [{{ $otherTask->phase_name }}] {{ $otherTask->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="f-assigned-to-3" class="block text-xs font-bold uppercase text-slate-500 mb-1">Assigned Technician</label>
                            <select id="f-assigned-to-3" name="assigned_to" x-model="editTaskAssignedTo" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                <option value="">Select Technician</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="f-estimated-hours-2" class="block text-xs font-bold uppercase text-slate-500 mb-1">Estimated Hours</label>
                            <input id="f-estimated-hours-2" type="number" step="0.5" min="0" name="estimated_hours" x-model="editTaskEstHours" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>
                    </div>

                    {{-- Conditional Blocker Reason --}}
                    <div x-show="editTaskStatus === 'blocked'" class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 space-y-1">
                        <label for="f-blocker-reason-2" class="block text-xs font-extrabold uppercase text-rose-700 dark:text-rose-300">Blocker Reason *</label>
                        <input id="f-blocker-reason-2" type="text" name="blocker_reason" x-model="editTaskBlockerReason" placeholder="e.g. Cannot start Camera Mounting until Client Finishes False Ceiling Paint" class="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-rose-300 dark:border-rose-800 rounded-lg outline-none font-medium">
                    </div>

                    {{-- Quick Log Material / Cable Action --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Field Material / Cable Logging:</span>
                        <button type="button" @click="editTaskModalOpen = false; openConsumeModal(editTaskId, editTaskTitle)" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100">
                            <svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Log Materials on this Task
                        </button>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="editTaskModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs shadow-md">Update Task</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- TWO-STEP SPLIT GUIDED ATTENDANCE MODAL COMPONENT --}}
    <x-mark-attendance-modal :workers="$workers" :projects="collect([$project])" :default-project-id="$project->id" />

    {{-- Sortable.js CDN for Drag-and-Drop Kanban Interaction --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
</x-app-layout>
