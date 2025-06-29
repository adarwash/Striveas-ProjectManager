<!-- Edit Calendar View -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1>Edit Calendar</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/dashboard/calendar">Calendar</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Calendar</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Edit Calendar Details</h5>
            </div>
            <div class="card-body">
                <?php flash('calendar_error'); ?>
                
                <form action="/dashboard/editCalendar/<?= $data['calendar']['id'] ?>" method="POST">
                    <div class="mb-3">
                        <label for="calendar_name" class="form-label">Calendar Name</label>
                        <input type="text" class="form-control" id="calendar_name" name="calendar_name" 
                               value="<?= $data['calendar']['name'] ?>" required>
                    </div>
                    
                    <?php if ($data['calendar']['source'] === 'ical'): ?>
                    <div class="mb-3">
                        <label for="calendar_url" class="form-label">Calendar URL</label>
                        <input type="url" class="form-control" id="calendar_url" name="calendar_url" 
                               value="<?= $data['calendar']['source_id'] ?>" required>
                        <div class="form-text">The URL must be a valid iCal feed (.ics file)</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="calendar_color" class="form-label">Display Color</label>
                        <input type="color" class="form-control form-control-color" id="calendar_color" 
                               name="calendar_color" value="<?= $data['calendar']['color'] ?>" title="Choose color">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="calendar_refresh" name="calendar_refresh" 
                               <?= isset($data['calendar']['auto_refresh']) && $data['calendar']['auto_refresh'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="calendar_refresh">
                            Auto-refresh calendar (sync daily)
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="calendar_active" name="calendar_active" 
                               <?= isset($data['calendar']['active']) && $data['calendar']['active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="calendar_active">
                            Active (display events from this calendar)
                        </label>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-muted mb-1">Source: <?= ucfirst($data['calendar']['source']) ?></p>
                        <p class="text-muted mb-1">Last synced: 
                            <?= isset($data['calendar']['last_synced']) && $data['calendar']['last_synced'] ? date('Y-m-d H:i:s', strtotime($data['calendar']['last_synced'])) : 'Never' ?>
                        </p>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/dashboard/calendar" class="btn btn-secondary">Cancel</a>
                        <div>
                            <a href="/dashboard/syncCalendar/<?= $data['calendar']['id'] ?>" 
                               class="btn btn-info me-2">Sync Now</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 