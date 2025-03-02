<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Manage Skills</h1>
                <a href="/profile" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Profile
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Select Your Skills</h5>
                    
                    <!-- Skill category filter -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item filter-item" href="#" data-category="all">All Skills</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item filter-item" href="#" data-category="programming">Programming</a></li>
                            <li><a class="dropdown-item filter-item" href="#" data-category="design">Design</a></li>
                            <li><a class="dropdown-item filter-item" href="#" data-category="management">Management</a></li>
                            <li><a class="dropdown-item filter-item" href="#" data-category="other">Other</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <form action="/profile/skills" method="POST">
                        <?php if (empty($all_skills)): ?>
                            <div class="alert alert-info">
                                No skills found in the system. Please contact an administrator to add skills.
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="skillSearch" placeholder="Search skills...">
                            </div>
                            
                            <div class="skills-container">
                                <?php 
                                // Get user's skill IDs for easy checking
                                $userSkillIds = [];
                                if (!empty($user_skills)) {
                                    foreach ($user_skills as $skill) {
                                        $userSkillIds[] = $skill['id'];
                                    }
                                }
                                
                                // Group skills by category
                                $skillsByCategory = [];
                                foreach ($all_skills as $skill) {
                                    $category = $skill['category'] ?? 'other';
                                    $skillsByCategory[$category][] = $skill;
                                }
                                
                                // Display skills by category
                                foreach ($skillsByCategory as $category => $skills): 
                                ?>
                                    <div class="skill-category mb-4" data-category="<?= htmlspecialchars(strtolower($category)) ?>">
                                        <h6 class="text-uppercase text-muted mb-3"><?= htmlspecialchars(ucfirst($category)) ?></h6>
                                        <div class="row">
                                            <?php foreach ($skills as $skill): ?>
                                                <div class="col-md-4 col-sm-6 mb-3 skill-item">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="skills[]" 
                                                               value="<?= $skill['id'] ?>" id="skill_<?= $skill['id'] ?>"
                                                               <?= in_array($skill['id'], $userSkillIds) ? 'checked' : '' ?>>
                                                        <label class="form-check-label skill-label" for="skill_<?= $skill['id'] ?>" 
                                                               title="<?= htmlspecialchars($skill['description'] ?? '') ?>">
                                                            <?= htmlspecialchars($skill['name']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="/profile" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Skills</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Skills search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('skillSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const skillItems = document.querySelectorAll('.skill-item');
            
            skillItems.forEach(item => {
                const label = item.querySelector('.skill-label');
                const skillName = label.textContent.toLowerCase();
                
                if (skillName.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide category headers based on visible items
            document.querySelectorAll('.skill-category').forEach(category => {
                const visibleItems = category.querySelectorAll('.skill-item[style=""]').length;
                category.style.display = visibleItems > 0 ? '' : 'none';
            });
        });
    }
    
    // Category filter
    const filterItems = document.querySelectorAll('.filter-item');
    filterItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.getAttribute('data-category');
            
            document.querySelectorAll('.skill-category').forEach(categoryEl => {
                if (category === 'all' || categoryEl.getAttribute('data-category') === category) {
                    categoryEl.style.display = '';
                } else {
                    categoryEl.style.display = 'none';
                }
            });
            
            // Update dropdown button text
            document.getElementById('filterDropdown').textContent = 'Filter: ' + this.textContent;
        });
    });
});
</script> 