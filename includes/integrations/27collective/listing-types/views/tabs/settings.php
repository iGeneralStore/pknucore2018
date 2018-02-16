<div class="sub-tabs">
	<ul>
		<li :class="currentTab == 'settings' && currentSubTab == 'general' ? 'active' : ''" class="settings-tab-general">
			<a @click.prevent="setTab('settings', 'general')">General</a>
		</li>
		<li :class="currentTab == 'settings' && currentSubTab == 'packages' ? 'active' : ''" class="settings-tab-packages">
			<a @click.prevent="setTab('settings', 'packages')">Packages</a>
		</li>
		<li :class="currentTab == 'settings' && currentSubTab == 'reviews' ? 'active' : ''" class="settings-tab-reviews">
			<a @click.prevent="setTab('settings', 'reviews')">Reviews</a>
		</li>
	</ul>
</div>

<div class="tab-content">
	<input type="hidden" v-model="settings_page_json_string" name="case27_listing_type_settings_page">

	<div class="settings-tab-general-content" v-show="currentSubTab == 'general'">
		<div class="listing-type-settings">
			<div class="column">
				<div class="card">
					<h4>Labels</h4>

					<div class="form-group">
						<label>Icon</label>
						<iconpicker v-model="settings.icon"></iconpicker>
					</div>

					<div class="form-group">
						<label>Singular name <small>(e.g. Business)</small></label>
						<input type="text" v-model="settings.singular_name">
					</div>

					<div class="form-group">
						<label>Plural name <small>(e.g. Businesses)</small></label>
						<input type="text" v-model="settings.plural_name">
					</div>
				</div>

				<div class="card">
					<h4>Tools</h4>
					<div class="form-group">
						<label>Listing type configuration</label><br>
						<a @click.prevent="exportConfig" class="btn btn-primary">Export config file</a>
						<a @click.prevent="startImportConfig" class="btn btn-plain">Import config file</a>
						<input type="file" name="c27-import-config" id="c27-import-config" @change="importConfig"
						onclick="return confirm('Imported configuration will overwrite your current settings. Do you want to proceed?')">
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="settings-tab-packages-content" v-show="currentSubTab == 'packages'">
		<div class="listing-type-packages">
			<div class="column">
				<div class="card">
					<h4>
						Listing Packages
						<p>Set what packages the user can choose from when submitting a listing of this type.</p>
					</h4>

					<div class="fields-wrapper">
						<draggable v-model="settings.packages.used" :options="{group: 'settings-packages', animation: 100, handle: 'h5'}" @start="drag=true" @end="drag=false" class="fields-draggable" :class="drag ? 'active' : ''">
							<div v-for="package in settings.packages.used" class="field">
								<h5>
									<span class="prefix">+</span>
									{{ packages().getPackageTitle(package) }}
									<small v-show="package.label.length">({{ packages().getPackageDefaultTitle(package) }})</small>
									<span class="actions">
										<span title="This package will be highlighted" class="highlighted" v-show="package.featured"><i class="mi star"></i></span>
										<span title="Remove" @click.prevent="packages().remove(package)"><i class="mi delete"></i></span>
									</span>
								</h5>
								<div class="edit">
									<div class="form-group">
										<label>Label</label>
										<input type="text" v-model="package.label" :placeholder="packages().getPackageDefaultTitle(package)">
										<p class="form-description">Leave blank to use the default package label.</p>
									</div>

									<div class="form-group">
										<label>Description</label>
										<textarea v-model="package.description" placeholder="Put each feature in a new line"></textarea>
										<p class="form-description">Leave blank to use the default package description.</p>
									</div>

									<div class="form-group">
										<label><input type="checkbox" v-model="package.featured"> Featured?</label>
										<p class="form-description">Featured packages will be highlighted.</p>
									</div>

									<div style="clear: both;"></div>

									<!-- <pre>{{ package }}</pre> -->
								</div>
							</div>

						</draggable>

						<div class="form-group field add-new-field">
							<label>List of packages</label>
							<div class="select-wrapper">
								<select v-model="state.settings.new_package">
									<option v-for="name, id in state.settings.packages" :value="id" v-if="! packages().isPackageUsed(id)">{{ name }}</option>
								</select>
							</div>

							<button class="btn btn-primary pull-right" @click.prevent="packages().add()">Add</button>
							<p class="form-description">You can create listing packages as WooCommerce products. The WC Paid Listings plugin is required for this.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="settings-tab-reviews-content" v-show="currentSubTab == 'reviews'">
		<div class="listing-type-reviews">
			<div class="column">
				<div class="card">
					<h4>
						Listing Reviews
						<p>Customize how listing reviews work, enable star ratings, add multiple rating categories, etc.</p>
					</h4>

					<div class="form-group">
						<label>
							<input type="checkbox" v-model="settings.reviews.gallery.enabled">
							Enable gallery upload
						</label>
					</div><br>

					<div class="form-group">
						<label>
							<input type="checkbox" v-model="settings.reviews.ratings.enabled">
							Enable star ratings
						</label>
					</div><br>

					<div class="form-group" v-show="settings.reviews.ratings.enabled">
						<label>Ratings mode</label>
						<label>
							<input type="radio" v-model="settings.reviews.ratings.mode" value="5">
							5 stars
						</label>
						<label>
							<input type="radio" v-model="settings.reviews.ratings.mode" value="10">
							10 stars
						</label>
					</div><br>

					<div class="fields-wrapper" v-show="settings.reviews.ratings.enabled">
						<div class="form-group">
							<label>Rating Categories</label>
						</div>

						<draggable v-model="settings.reviews.ratings.categories" :options="{group: 'settings-reviews-categories', animation: 100, handle: 'h5'}" @start="drag=true" @end="drag=false" class="fields-draggable" :class="drag ? 'active' : ''">
							<div v-for="category in settings.reviews.ratings.categories" class="field">
								<h5>
									<span class="prefix">+</span>
									{{ category.label }}
									<span class="actions" v-show="settings.reviews.ratings.categories.length > 1 && category.id !== 'rating'">
										<span title="Remove" @click.prevent="reviews().removeCategory(category)"><i class="mi delete"></i></span>
									</span>
								</h5>
								<div class="edit">
									<div class="form-group">
										<label>Label</label>
										<input type="text" v-model="category.label" @input="category.is_new ? category.id = slugify( category.label ) : null">
									</div>

									<div class="form-group">
										<label>Key</label>
										<input type="text" v-model="category.id" @input="category.is_new ? category.id = slugify( category.id ) : null" :disabled="!category.is_new">
										<p class="form-description" v-show="category.is_new">Needs to be unique. This isn't visible to the user.</p>
									</div>

									<div style="clear: both;"></div>

									<!-- <pre>{{ category }}</pre> -->
								</div>
							</div>

						</draggable>

						<div class="form-group">
							<button class="btn btn-primary pull-right" @click.prevent="reviews().addCategory()">Add rating category</button>
						</div>

						<div style="clear: both;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- <pre>{{ settings }}</pre> -->
