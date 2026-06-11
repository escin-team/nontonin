<div class="row">
    <div class="col-12">
        <h2 class="section-title"><i class="fas fa-fire"></i> Trending Drama China</h2>
    </div>
</div>

<?php if (!empty($trending)): ?>
<div class="row mb-5">
    <?php foreach ($trending as $show): ?>
    <div class="col-6 col-md-4 col-lg-2 mb-4">
        <div class="card">
            <img src="<?php echo isset($show['poster']) ? htmlspecialchars($show['poster']) : BASE_URL . '/assets/images/placeholder.jpg'; ?>" 
                 class="card-img-top" alt="<?php echo htmlspecialchars($show['title']); ?>">
            <div class="card-body p-2">
                <h6 class="card-title text-truncate">
                    <a href="<?php echo BASE_URL; ?>/drama/<?php echo $show['slug']; ?>" class="text-white text-decoration-none">
                        <?php echo htmlspecialchars($show['title']); ?>
                    </a>
                </h6>
                <small class="text-muted"><?php echo isset($show['year']) ? $show['year'] : ''; ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">
    <p>Loading trending dramas...</p>
</div>
<?php endif; ?>

<div class="row mt-5">
    <div class="col-12">
        <h2 class="section-title"><i class="fas fa-clock"></i> Recently Added</h2>
    </div>
</div>

<?php if (!empty($recent)): ?>
<div class="row">
    <?php foreach ($recent as $show): ?>
    <div class="col-6 col-md-4 col-lg-2 mb-4">
        <div class="card">
            <img src="<?php echo htmlspecialchars($show['poster_url']); ?>" 
                 class="card-img-top" alt="<?php echo htmlspecialchars($show['title']); ?>">
            <div class="card-body p-2">
                <h6 class="card-title text-truncate">
                    <a href="<?php echo BASE_URL; ?>/drama/<?php echo $show['slug']; ?>" class="text-white text-decoration-none">
                        <?php echo htmlspecialchars($show['title']); ?>
                    </a>
                </h6>
                <small class="text-muted"><?php echo $show['category_name']; ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">
    <p>No shows available yet. Check back later!</p>
</div>
<?php endif; ?>
