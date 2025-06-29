<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/sites">Sites</a></li>
            <li class="breadcrumb-item"><a href="/sites/viewSite/<?= $data['site']['id'] ?>"><?= $data['site']['name'] ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Assign Employees</li>
        </ol>
    </nav>
    
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">Assign Employees to Site</h1>
            <p class="text-muted"><?= $data['site']['name'] ?> - <?= $data['site']['location'] ?></p>
        </div>
        <div class="d-flex">
            <a href="/sites/viewSite/<?= $data['site']['id'] ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Site
            </a>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php flash('site_success'); ?>
    <?php flash('site_error'); ?>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Select Employees</h5>
            <div class="form-group has-search">
                <span class="bi bi-search form-control-feedback"></span>
                <input type="text" class="form-control" placeholder="Search employees..." id="employeeSearch">
            </div>
        </div>
        <div class="card-body">
            <form action="/sites/assignEmployees/<?= $data['site']['id'] ?>" method="post">
                <!-- Quick Selection Buttons -->
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="selectAll">Select All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="deselectAll">Deselect All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="invertSelection">Invert Selection</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="employeesTable">
                        <thead>
                            <tr>
                                <th width="50">Select</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Site Assignment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['employees'])): ?>
                                <?php foreach ($data['employees'] as $employee): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input employee-checkbox" type="checkbox" 
                                                       name="employees[]" value="<?= $employee['id'] ?>"
                                                       <?= in_array($employee['id'], $data['assignedIds']) ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                        <td><?= $employee['full_name'] ?? $employee['username'] ?></td>
                                        <td><?= $employee['email'] ?></td>
                                        <td><?= ucfirst($employee['role']) ?></td>
                                        <td>
                                            <?php if (in_array($employee['id'], $data['assignedIds'])): ?>
                                                <span class="badge bg-success">Currently Assigned</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No employees found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Assignments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('employeeSearch').addEventListener('keyup', function() {
    var searchText = this.value.toLowerCase();
    var table = document.getElementById('employeesTable');
    var rows = table.getElementsByTagName('tr');
    
    for (var i = 1; i < rows.length; i++) {
        var employeeText = rows[i].textContent.toLowerCase();
        if (employeeText.indexOf(searchText) > -1) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
});

// Select All button
document.getElementById('selectAll').addEventListener('click', function() {
    var checkboxes = document.getElementsByClassName('employee-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = true;
    }
});

// Deselect All button
document.getElementById('deselectAll').addEventListener('click', function() {
    var checkboxes = document.getElementsByClassName('employee-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = false;
    }
});

// Invert Selection button
document.getElementById('invertSelection').addEventListener('click', function() {
    var checkboxes = document.getElementsByClassName('employee-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = !checkboxes[i].checked;
    }
});
</script> 