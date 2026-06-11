<div class="row">
    <div class="col-12 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/home" class="text-white">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($show['title']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <!-- Drama Info -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <img src="<?php echo isset($show['poster_url']) && !empty($show['poster_url']) ? htmlspecialchars($show['poster_url']) : BASE_URL . '/assets/images/placeholder.jpg'; ?>" 
                 class="card-img-top" alt="<?php echo htmlspecialchars($show['title']); ?>">
            <div class="card-body">
                <h5 class="card-title text-center"><?php echo htmlspecialchars($show['title']); ?></h5>
                <p class="text-center text-muted">
                    <?php if (isset($show['release_year']) && $show['release_year']): ?>
                        <?php echo $show['release_year']; ?>
                    <?php endif; ?>
                    <?php if (isset($show['status'])): ?>
                        | <span class="badge badge-info"><?php echo ucfirst($show['status']); ?></span>
                    <?php endif; ?>
                </p>
                <div class="text-center">
                    <span class="badge badge-secondary"><?php echo htmlspecialchars($show['category_name']); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Synopsis & Episodes -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title text-primary">Sinopsis</h4>
                <p class="card-text" style="line-height: 1.8;">
                    <?php echo isset($show['synopsis']) && !empty($show['synopsis']) ? nl2br(htmlspecialchars($show['synopsis'])) : 'Sinopsis tidak tersedia.'; ?>
                </p>
            </div>
        </div>
        
        <!-- Episodes List -->
        <div class="card">
            <div class="card-header bg-dark border-secondary">
                <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Episode</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($episodes)): ?>
                <div class="row">
                    <?php foreach ($episodes as $episode): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/watch/<?php echo $show['slug']; ?>/<?php echo isset($episode['api_episode_id']) ? urlencode($episode['api_episode_id']) : $episode['episode_number']; ?>" 
                           class="btn btn-outline-primary btn-block" 
                           style="border-radius: 10px; padding: 15px 10px;">
                            <i class="fas fa-play-circle"></i><br>
                            <small>Episode <?php echo $episode['episode_number']; ?></small>
                            <?php if (isset($episode['title']) && !empty($episode['title'])): ?>
                                <br><small class="text-muted text-truncate" style="display:block; max-width:100%; overflow:hidden; text-overflow:ellipsis;">
                                    <?php echo htmlspecialchars($episode['title']); ?>
                                </small>
                            <?php endif; ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Episode belum tersedia. Silakan coba lagi nanti.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.breadcrumb-item + .breadcrumb-item::before {
    color: #6c757d;
}
.btn-outline-primary:hover {
    background-color: #e94560;
    border-color: #e94560;
    color: #fff;
}
.card {
    margin-bottom: 20px;
}
</style>
