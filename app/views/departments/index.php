<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Departments</h1>
            <a href="/departments/create" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> New Department
            </a>
        </div>
    </div>
</div>

<!-- Header and Stats Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Budget Overview</h5>
                    
                    <!-- Currency Selector -->
                    <div class="form-inline">
                        <label for="currencySelector" class="mr-2">View in:</label>
                        <select id="currencySelector" class="form-control form-control-sm">
                            <option value="USD" selected>USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                        </select>
                    </div>
                </div>
                
                <?php
                // Define currency symbols
                $currencySymbols = [
                    'USD' => '$',
                    'GBP' => '£',
                    'EUR' => '€'
                ];
                
                // Default currency is USD
                $defaultCurrency = '$';
                
                // Calculate total stats
                $totalBudget = !empty($data['budget_stats']->total_budget) ? $data['budget_stats']->total_budget : 0;
                $totalUsedBudget = !empty($data['budget_stats']->total_used_budget) ? $data['budget_stats']->total_used_budget : 0;
                $totalRemainingBudget = $totalBudget - $totalUsedBudget;
                $percentageUsed = ($totalBudget > 0) ? ($totalUsedBudget / $totalBudget) * 100 : 0;
                ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Budget</h6>
                                <h4 class="card-title budget-amount" data-usd-value="<?= $totalBudget ?>">
                                    <?= $defaultCurrency . number_format($totalBudget, 2) ?>
                                </h4>
                                <small>Across all departments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Allocated Budget</h6>
                                <h4 class="card-title budget-amount" data-usd-value="<?= $totalUsedBudget ?>">
                                    <?= $defaultCurrency . number_format($totalUsedBudget, 2) ?>
                                </h4>
                                <small>In active projects</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Remaining Budget</h6>
                                <h4 class="card-title budget-amount" data-usd-value="<?= $totalRemainingBudget ?>">
                                    <?= $defaultCurrency . number_format($totalRemainingBudget, 2) ?>
                                </h4>
                                <div class="progress mt-2">
                                    <div class="progress-bar <?= ($percentageUsed > 90) ? 'bg-danger' : (($percentageUsed > 70) ? 'bg-warning' : 'bg-success') ?>" role="progressbar" style="width: <?= $percentageUsed ?>%" aria-valuenow="<?= $percentageUsed ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?= number_format($percentageUsed, 1) ?>% of budget used</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Departments Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">All Departments</h5>
                    <a href="/departments/create" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> New Department
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Department</th>
                                <th>Budget</th>
                                <th>Used</th>
                                <th>Remaining</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data['departments'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No departments found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($data['departments'] as $department): ?>
                                    <?php
                                    // Calculate department metrics
                                    $budget = $department->budget;
                                    $usedBudget = $department->used_budget;
                                    $remainingBudget = $budget - $usedBudget;
                                    $percentageUsed = ($budget > 0) ? ($usedBudget / $budget) * 100 : 0;
                                    
                                    // Determine currency symbol (handle if currency column doesn't exist yet)
                                    $currencyCode = property_exists($department, 'currency') ? $department->currency : 'USD';
                                    $currencySymbol = isset($currencySymbols[$currencyCode]) ? $currencySymbols[$currencyCode] : '$';
                                    
                                    // Determine progress color based on percentage
                                    $progressColor = ($percentageUsed > 90) ? 'bg-danger' : (($percentageUsed > 70) ? 'bg-warning' : 'bg-success');
                                    if ($remainingBudget < 0) {
                                        $progressColor = 'bg-danger';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <a href="/departments/show/<?= $department->id ?>" class="font-weight-bold">
                                                    <?= htmlspecialchars($department->name) ?>
                                                </a>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars($department->description) ?></small>
                                        </td>
                                        <td>
                                            <span class="dept-budget" 
                                                  data-value="<?= $budget ?>" 
                                                  data-currency="<?= $currencyCode ?>">
                                                <?= $currencySymbol . number_format($budget, 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="dept-budget" 
                                                  data-value="<?= $usedBudget ?>" 
                                                  data-currency="<?= $currencyCode ?>">
                                                <?= $currencySymbol . number_format($usedBudget, 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="dept-budget <?= $remainingBudget < 0 ? 'text-danger' : '' ?>" 
                                                  data-value="<?= $remainingBudget ?>" 
                                                  data-currency="<?= $currencyCode ?>">
                                                <?= $currencySymbol . number_format($remainingBudget, 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="progress">
                                                <div class="progress-bar <?= $progressColor ?>" role="progressbar" style="width: <?= min($percentageUsed, 100) ?>%" aria-valuenow="<?= $percentageUsed ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-muted"><?= number_format($percentageUsed, 1) ?>% used</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="/departments/show/<?= $department->id ?>" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="/departments/edit/<?= $department->id ?>" class="btn btn-sm btn-warning">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Currency Conversion JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Define conversion rates (simplified for example)
    const conversionRates = {
        'USD': { 'USD': 1, 'EUR': 0.85, 'GBP': 0.75 },
        'EUR': { 'USD': 1.18, 'EUR': 1, 'GBP': 0.88 },
        'GBP': { 'USD': 1.33, 'EUR': 1.14, 'GBP': 1 }
    };
    
    // Currency symbols
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£'
    };
    
    // Get currency selector element
    const currencySelector = document.getElementById('currencySelector');
    
    // Function to convert amount from one currency to another
    function convertCurrency(amount, fromCurrency, toCurrency) {
        return amount * conversionRates[fromCurrency][toCurrency];
    }
    
    // Function to update displayed amounts based on selected currency
    function updateDisplayedAmounts(selectedCurrency) {
        // Update budget overview amounts
        document.querySelectorAll('.budget-amount').forEach(function(element) {
            const usdValue = parseFloat(element.getAttribute('data-usd-value'));
            const convertedValue = convertCurrency(usdValue, 'USD', selectedCurrency);
            element.textContent = currencySymbols[selectedCurrency] + convertedValue.toFixed(2);
        });
        
        // Update department table amounts
        document.querySelectorAll('.dept-budget').forEach(function(element) {
            const value = parseFloat(element.getAttribute('data-value'));
            const currency = element.getAttribute('data-currency');
            
            // Convert from department's currency to selected currency
            const convertedValue = convertCurrency(value, currency, selectedCurrency);
            
            if (convertedValue < 0) {
                element.classList.add('text-danger');
            } else {
                element.classList.remove('text-danger');
            }
            
            element.textContent = currencySymbols[selectedCurrency] + convertedValue.toFixed(2);
        });
    }
    
    // Add event listener to currency selector
    currencySelector.addEventListener('change', function() {
        updateDisplayedAmounts(this.value);
    });
});
</script> 