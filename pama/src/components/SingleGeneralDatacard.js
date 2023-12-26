import * as React from "react";
import useSwitchRoute from "../hooks/useSwitchRoute";

import {
  Card,
  CardContent,
  Typography,
  Button,
  CardHeader,
  CardActionArea,
  CardActions,
} from "@mui/material";

export default function SingleGeneralDatacard({ ...props }) {
  const switchRoute = useSwitchRoute();
  const { addeddate, name, aircharge, id, deleteCallback, formroute } = props;

  return (
    <Card sx={{ maxWidth: `100%`, width: `100%`, mb: 1.5 }}>
      <CardActionArea>
        <CardHeader
          title={`${addeddate}`}
          sx={{
            background: `#1c4e80`,
            color: `#ffffff`,
          }}
        />
        <CardContent
          sx={{
            background: `#F1F1F1`,
          }}
        >
          <Typography gutterBottom variant="h5" component="div">
          <b>Name:</b> {name}
          </Typography>
          <Typography gutterBottom variant="h5" component="div">
            <b>Air Charge:</b> {aircharge}
          </Typography>
        </CardContent>
      </CardActionArea>
      <CardActions
        sx={{
          background: `#DADADA`,
          display: `flex`,
          justifyContent: `space-between`,
        }}
      >
        <Button
          size="small"
          color="primary"
          onClick={() => switchRoute(`/${formroute}?mode=edit&id=${id}`, false)}
        >
          Edit
        </Button>
        <Button size="small" color="primary" onClick={deleteCallback}>
          Delete
        </Button>
      </CardActions>
    </Card>
  );
}
