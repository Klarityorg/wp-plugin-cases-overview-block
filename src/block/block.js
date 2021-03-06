import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { ServerSideRender } = wp.editor;

const el = wp.element.createElement;
const iconEl = el('svg', { width: 128, height: 128, viewBox: "0 0 128 128" },
	el('rect', { x: 0, y: 0, width: 128, height: 128, stroke: "white" }),
	el('path', { d: "M41.7607 39.0615H52.8432V60.866L73.2637 39.0615H86.6547L66.1434 60.2237L87.5885 88.9388H74.2753L58.66 67.706L52.8432 73.6982V88.9388H41.7607V39.0615Z", fill: "white" })
);

const layouts = {
	case_list: {
		name: 'Case list',
		description: 'The following cases will be shown :'
	},
	subcase_list: {
		name: 'Subcase list',
		description: 'The following sub-cases will be shown :'
	}
};

const filters = {
	in_sub_pages: {
		name: 'Cases in sub-pages'
	},
	all: {
		name: 'All cases'
	}
};

const caseStatusDescriptions = {
	unresolved: "Will match pages with metadata 'case_status' set to 'new', 'ongoing' or 'update'",
	resolved: "Will match pages with metadata 'case_status' set to 'resolved'"
};

registerBlockType('klarity/klarity-cases-overview-block', {
	title: __('Cases overview'),
	category: 'layout',
	icon: iconEl,

	attributes: {
		layout: {
			type: 'string',
			default: 'case_list'
		},
		filter: {
			type: 'string',
			default: 'in_sub_pages'
		},
		showUnresolved: {
			type: 'boolean',
			default: true
		},
		showResolved: {
			type: 'boolean',
			default: true
		},
	},

	edit: props => {
		let {attributes: {layout, filter, showUnresolved, showResolved}, setAttributes} = props;

		const setLayout = event => {
			const selected = event.target.querySelector('option:checked');
			setAttributes({layout: selected.value});
			event.preventDefault();
		};

		const setFilter = event => {
			const selected = event.target.querySelector('option:checked');
			setAttributes({filter: selected.value});
			event.preventDefault();
		};

		const setShowUnresolved = event => {
			showUnresolved = event.target.checked;
			setAttributes({showUnresolved: showUnresolved});
		};

		const setShowResolved = event => {
			showResolved = event.target.checked;
			setAttributes({showResolved});
		};

		if (showUnresolved === undefined) { showUnresolved = true; }
		if (showResolved === undefined) { showResolved = true; }

		return !layouts[layout]? <span>Invalid layout : {layout}</span> : <form id="action_edit">
			<div className="form-group">
				<label>Layout:
					<select value={layout} onChange={setLayout}>
						{Object.keys(layouts).map((layoutId) => (
							<option value={layoutId} selected>{layouts[layoutId].name}</option>
						))}
					</select>
				</label>
			</div>
			<div className="form-group">
				<label title={caseStatusDescriptions.unresolved}>
					<input type="checkbox" defaultChecked={showUnresolved} value={showUnresolved} onChange={setShowUnresolved} />Unresolved cases
				</label>&nbsp;
				<label title={caseStatusDescriptions.resolved}>
					<input type="checkbox" defaultChecked={showResolved} value={showResolved} onChange={setShowResolved} />Resolved cases
				</label>
			</div>
			{layout === 'case_list' && <div className="form-group">
				<label>Show:
					<select value={filter} onChange={setFilter}>
						{Object.keys(filters).map((filterId) => (
							<option value={filterId} selected>{filters[filterId].name}</option>
						))}
					</select>
				</label>
			</div>}
			<div>{layouts[layout].description}</div>
			<ServerSideRender
				block="klarity/klarity-cases-overview-block"
				attributes={ props.attributes } />
		</form>;
	},

	save: () => {
		// Rendering in PHP
		return null;
	},
} );
