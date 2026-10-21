<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['news_title'])) {
		$valid = 0;
		$error_message .= 'News title can not be empty<br>';
	} else {
		// Duplicate Checking
		$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_news WHERE news_title=?");
    	$statement->execute(array($_POST['news_title']));
		$total = (int) $statement->fetchColumn();
    	if($total) {
    		$valid = 0;
        	$error_message .= "News title already exists<br>";
    	}
		$title_length = function_exists('mb_strlen') ? mb_strlen($_POST['news_title'], 'UTF-8') : strlen($_POST['news_title']);
		if($title_length > 255) {
			$valid = 0;
			$error_message .= 'News title must not exceed 255 characters.<br>';
		}
	}

	if(empty($_POST['news_content'])) {
		$valid = 0;
		$error_message .= 'News content can not be empty<br>';
	}

	if(empty($_POST['news_content_short'])) {
		$valid = 0;
		$error_message .= 'News content (short) can not be empty<br>';
	}

	if(empty($_POST['news_date'])) {
		$valid = 0;
		$error_message .= 'News publish date can not be empty<br>';
	} else {
		$parsed_date = DateTime::createFromFormat('d-m-Y', $_POST['news_date']);
		if(!$parsed_date || $parsed_date->format('d-m-Y') !== $_POST['news_date']) {
			$valid = 0;
			$error_message .= 'News publish date must use a valid dd-mm-yyyy format.<br>';
		}
	}

	if(empty($_POST['category_id'])) {
		$valid = 0;
		$error_message .= 'You must have to select a category<br>';
	} else {
		$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_category WHERE category_id=?");
		$statement->execute(array($_POST['category_id']));
		if((int) $statement->fetchColumn() < 1) {
			$valid = 0;
			$error_message .= 'The selected category is not valid.<br>';
		}
	}


	$publisher = trim(isset($_POST['publisher']) ? $_POST['publisher'] : '');
	if($publisher === '') {
		$publisher = 'Admin STBA';
	}
	$publisher_length = function_exists('mb_strlen') ? mb_strlen($publisher, 'UTF-8') : strlen($publisher);
	if($publisher_length > 255) {
		$valid = 0;
		$error_message .= 'Publisher must not exceed 255 characters.<br>';
	}
	$meta_title_length = function_exists('mb_strlen') ? mb_strlen($_POST['meta_title'], 'UTF-8') : strlen($_POST['meta_title']);
	if($meta_title_length > 255) {
		$valid = 0;
		$error_message .= 'Meta title must not exceed 255 characters.<br>';
	}


	$path = isset($_FILES['photo']['name']) ? $_FILES['photo']['name'] : '';
	$path_tmp = isset($_FILES['photo']['tmp_name']) ? $_FILES['photo']['tmp_name'] : '';
	$ext = '';


    if($path!='') {
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $file_name = basename( $path, '.' . $ext );
		if(!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true)) {
            $valid = 0;
			$error_message .= 'Featured photo must be a JPG, JPEG, PNG, GIF, or WEBP file.<br>';
        }
    }
	

	if($valid == 1) {
		if($_POST['news_slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['news_title']);
    		$news_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($_POST['news_slug']);
    		$news_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

		// Keep appending a number until the slug is genuinely unique.
		$base_slug = substr(trim($news_slug, '-'), 0, 240);
		if($base_slug === '') {
			$base_slug = 'news';
		}
		$news_slug = $base_slug;
		$suffix = 1;
		$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_news WHERE news_slug=?");
		do {
			$statement->execute(array($news_slug));
			$slug_exists = (int) $statement->fetchColumn() > 0;
			if($slug_exists) {
				$news_slug = $base_slug . '-' . $suffix;
				$suffix++;
			}
		} while($slug_exists);

		$final_name = '';
		$final_path = '';
		try {
			$pdo->beginTransaction();

			// Insert first so lastInsertId() supplies a reliable, concurrency-safe photo name.
			$statement = $pdo->prepare("INSERT INTO tbl_news (news_title,news_slug,news_content,news_content_short,news_date,photo,category_id,publisher,total_view,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
			$statement->execute(array($_POST['news_title'],$news_slug,$_POST['news_content'],$_POST['news_content_short'],$_POST['news_date'],'',$_POST['category_id'],$publisher,0,$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description']));
			$news_id = (int) $pdo->lastInsertId();
			if($news_id < 1) {
				throw new RuntimeException('The new news ID could not be generated.');
			}

			if($path !== '') {
				$final_name = 'news-' . $news_id . '.' . $ext;
				$final_path = __DIR__ . '/../assets/uploads/' . $final_name;
				if(!move_uploaded_file($path_tmp, $final_path)) {
					throw new RuntimeException('The featured photo could not be saved. Check the uploads folder permissions.');
				}

				$statement = $pdo->prepare("UPDATE tbl_news SET photo=? WHERE news_id=?");
				$statement->execute(array($final_name, $news_id));
			}

			$pdo->commit();
			$success_message = 'News is added successfully!';
		} catch(Throwable $error) {
			if($pdo->inTransaction()) {
				$pdo->rollBack();
			}
			if($final_path !== '' && is_file($final_path)) {
				unlink($final_path);
			}
			error_log('Adding news failed: ' . $error->getMessage());
			$error_message .= 'News could not be saved. Please check the entered data and featured photo, then try again.<br>';
		}
	}
}

$retain_news_form = isset($_POST['form1']) && $success_message === '';
$news_form_values = $retain_news_form ? $_POST : array();
$news_form_value = function($name, $default = '') use ($news_form_values) {
	return isset($news_form_values[$name]) ? (string) $news_form_values[$name] : (string) $default;
};
$news_form_e = function($name, $default = '') use ($news_form_value) {
	return htmlspecialchars($news_form_value($name, $default), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Add News</h1>
	</div>
	<div class="content-header-right">
		<a href="news.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>


<section class="content">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p>
				<?php echo $error_message; ?>
				</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<div class="callout callout-info">
				<p><strong>Draft protection is active.</strong> Text fields are saved automatically in this browser. If saving fails, your entries will remain available. For security reasons, a featured photo must be selected again after a failed submission.</p>
			</div>

			<form class="form-horizontal" id="news-add-form" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">News Title <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="news_title" maxlength="255" required value="<?php echo $news_form_e('news_title'); ?>" placeholder="Example: News Headline">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">News Slug </label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="news_slug" maxlength="255" value="<?php echo $news_form_e('news_slug'); ?>" placeholder="Example: news-headline">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">News Content <span>*</span></label>
							<div class="col-sm-8">
								<textarea class="form-control editor" name="news_content"><?php echo $news_form_e('news_content'); ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">News Content (Short) <span>*</span></label>
							<div class="col-sm-8">
								<textarea class="form-control" name="news_content_short" required style="height:100px;"><?php echo $news_form_e('news_content_short'); ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">News Publish Date <span>*</span></label>
							<div class="col-sm-2">
								<input type="text" class="form-control" name="news_date" id="datepicker" required value="<?php echo $news_form_e('news_date', date('d-m-Y')); ?>">(Format: dd-mm-yyyy)
							</div>
						</div>
						<div class="form-group">
				            <label for="" class="col-sm-3 control-label">Featured Photo</label>
				            <div class="col-sm-6" style="padding-top:6px;">
				                <input type="file" name="photo">
				            </div>
				        </div>
						<div class="form-group">
				            <label for="" class="col-sm-3 control-label">Select Category <span>*</span></label>
				            <div class="col-sm-3">
			<select class="form-control select2" name="category_id">
				            		<option value="">Select a category</option>
				            		<?php
						            	$i=0;
						            	$statement = $pdo->prepare("SELECT * FROM tbl_category ORDER BY category_name ASC");
						            	$statement->execute();
						            	$result = $statement->fetchAll();
						            	foreach ($result as $row) {
						            		?>
											<option value="<?php echo $row['category_id']; ?>"<?php echo $news_form_value('category_id') === (string) $row['category_id'] ? ' selected' : ''; ?>><?php echo $row['category_name']; ?></option>
						            		<?php
						            	}
					            	?>
				            	</select>
				            </div>
				        </div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Publisher </label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="publisher" maxlength="255" value="<?php echo $news_form_e('publisher'); ?>"> (If left blank, Admin STBA will be used as the publisher)
							</div>
						</div>
						<h3 class="seo-info">SEO Information</h3>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Meta Title </label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="meta_title" maxlength="255" value="<?php echo $news_form_e('meta_title'); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Meta Keywords </label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="meta_keyword" value="<?php echo $news_form_e('meta_keyword'); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Meta Description </label>
							<div class="col-sm-8">
								<textarea class="form-control" name="meta_description" style="height:200px;"><?php echo $news_form_e('meta_description'); ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Submit</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

</section>

<script>
(function () {
	var draftKey = 'stba-news-add-draft-v1';
	var form;
	var fields = ['news_title', 'news_slug', 'news_content', 'news_content_short', 'news_date', 'category_id', 'publisher', 'meta_title', 'meta_keyword', 'meta_description'];
	var retainServerValues = <?php echo $retain_news_form ? 'true' : 'false'; ?>;
	var saveSucceeded = <?php echo $success_message !== '' ? 'true' : 'false'; ?>;
	var saveTimer;

	function editor() {
		return window.CKEDITOR && CKEDITOR.instances.news_content ? CKEDITOR.instances.news_content : null;
	}

	function collectDraft() {
		var draft = {};
		for (var i = 0; i < fields.length; i++) {
			var element = form.elements[fields[i]];
			if (element) draft[fields[i]] = element.value;
		}
		var contentEditor = editor();
		if (contentEditor) draft.news_content = contentEditor.getData();
		return draft;
	}

	function saveDraft() {
		clearTimeout(saveTimer);
		saveTimer = setTimeout(function () {
			try { localStorage.setItem(draftKey, JSON.stringify(collectDraft())); } catch (error) {}
		}, 250);
	}

	function restoreDraft() {
		if (retainServerValues || saveSucceeded) return;
		try {
			var saved = JSON.parse(localStorage.getItem(draftKey) || 'null');
			if (!saved) return;
			for (var i = 0; i < fields.length; i++) {
				var name = fields[i];
				var element = form.elements[name];
				if (element && typeof saved[name] === 'string') element.value = saved[name];
			}
			var contentEditor = editor();
			if (contentEditor && typeof saved.news_content === 'string') contentEditor.setData(saved.news_content);
			if (window.jQuery) jQuery(form.elements.category_id).trigger('change');
		} catch (error) {}
	}

	window.addEventListener('load', function () {
		form = document.getElementById('news-add-form');
		if (!form) return;
		if (saveSucceeded) {
			try { localStorage.removeItem(draftKey); } catch (error) {}
			return;
		}

		restoreDraft();
		form.addEventListener('input', saveDraft);
		form.addEventListener('change', saveDraft);
		var contentEditor = editor();
		if (contentEditor) contentEditor.on('change', saveDraft);

		form.addEventListener('submit', function (event) {
			var activeEditor = editor();
			if (activeEditor) {
				activeEditor.updateElement();
			}
			var required = [
				['news_title', 'News Title'],
				['news_content', 'News Content'],
				['news_content_short', 'News Content (Short)'],
				['news_date', 'News Publish Date'],
				['category_id', 'Category']
			];
			for (var i = 0; i < required.length; i++) {
				var element = form.elements[required[i][0]];
				if (!element || !element.value.trim()) {
					event.preventDefault();
					alert(required[i][1] + ' wajib diisi. Draft Anda tetap tersimpan.');
					if (required[i][0] === 'news_content' && activeEditor) activeEditor.focus();
					else if (element) element.focus();
					return;
				}
			}
			try { localStorage.setItem(draftKey, JSON.stringify(collectDraft())); } catch (error) {}
		});
	});
}());
</script>

<?php require_once('footer.php'); ?>
